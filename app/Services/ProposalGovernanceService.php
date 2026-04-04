<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\ProposalVote;
use App\Models\User;
use App\Notifications\ProposalFinalizedNotification;
use Carbon\Carbon;

class ProposalGovernanceService
{
    public function finalizeEligible(): int
    {
        $count = 0;

        Proposal::query()
            ->where('status', 'active')
            ->whereNull('finalized_at')
            ->withCount([
                'votes as yes_votes_count' => fn ($q) => $q->where('vote', 'yes'),
                'votes as no_votes_count' => fn ($q) => $q->where('vote', 'no'),
            ])
            ->get()
            ->each(function (Proposal $proposal) use (&$count): void {
                if ($this->finalizeOne($proposal)) {
                    $count++;
                }
            });

        return $count;
    }

    public function finalizeOne(Proposal $proposal, ?User $actor = null, bool $force = false): bool
    {
        if ($proposal->status !== 'active' || $proposal->finalized_at !== null) {
            return false;
        }

        $proposal->loadCount([
            'votes as yes_votes_count' => fn ($q) => $q->where('vote', 'yes'),
            'votes as no_votes_count' => fn ($q) => $q->where('vote', 'no'),
        ]);

        $yes = (int) $proposal->yes_votes_count;
        $no = (int) $proposal->no_votes_count;
        $total = $yes + $no;
        $quorum = max(1, (int) $proposal->quorum_required);

        $deadlineReached = $proposal->closes_at !== null && Carbon::parse($proposal->closes_at)->isPast();
        $quorumReached = $total >= $quorum;

        if (! $force && ! $deadlineReached && ! $quorumReached) {
            return false;
        }

        $status = ($quorumReached && $yes > $no) ? 'approved' : 'rejected';

        $proposal->update([
            'status' => $status,
            'finalized_at' => now(),
        ]);

        $logActor = $actor
            ?? User::query()->where('role', 'admin')->orderBy('id')->first()
            ?? $proposal->proposer;

        app(ActivityLogService::class)->proposalFinalized(
            $logActor,
            $proposal->fresh(),
            $yes,
            $no,
            $quorum,
            $force
        );

        $proposal->load('proposer');

        $voterIds = ProposalVote::query()
            ->where('proposal_id', $proposal->id)
            ->pluck('user_id')
            ->all();

        $recipientIds = collect([$proposal->proposed_by])
            ->merge($voterIds)
            ->filter()
            ->unique()
            ->values();

        /** @var \Illuminate\Database\Eloquent\Collection<int, User> $recipients */
        $recipients = User::query()->findMany($recipientIds->all());

        foreach ($recipients as $recipient) {
            $recipient->notify(new ProposalFinalizedNotification(
                $proposal->fresh(),
                $yes,
                $no,
                $quorum,
                $force
            ));
        }

        return true;
    }
}
