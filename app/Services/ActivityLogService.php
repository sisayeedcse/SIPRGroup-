<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Announcement;
use App\Models\Document;
use App\Models\Investment;
use App\Models\Proposal;
use App\Models\Transaction;
use App\Models\User;

class ActivityLogService
{
    public function transactionCreated(User $actor, Transaction $transaction): void
    {
        $this->write(
            $actor,
            'tx-create',
            sprintf(
                'Created %s transaction of %.2f for member #%d on %s.',
                $transaction->type,
                (float) $transaction->amount,
                $transaction->user_id,
                optional($transaction->date)->format('Y-m-d') ?? 'n/a'
            )
        );
    }

    public function transactionUpdated(User $actor, Transaction $before, Transaction $after): void
    {
        $this->write(
            $actor,
            'tx-update',
            sprintf(
                'Updated transaction #%d from %s %.2f (member #%d) to %s %.2f (member #%d).',
                $after->id,
                $before->type,
                (float) $before->amount,
                $before->user_id,
                $after->type,
                (float) $after->amount,
                $after->user_id
            )
        );
    }

    public function transactionDeleted(User $actor, Transaction $transaction): void
    {
        $this->write(
            $actor,
            'tx-delete',
            sprintf(
                'Deleted %s transaction #%d amount %.2f for member #%d.',
                $transaction->type,
                $transaction->id,
                (float) $transaction->amount,
                $transaction->user_id
            )
        );
    }

    public function memberUpdated(User $actor, User $before, User $after): void
    {
        $this->write(
            $actor,
            'member-update',
            sprintf(
                'Updated member #%d role %s->%s, status %s->%s, locked %s->%s.',
                $after->id,
                $before->role->value ?? (string) $before->role,
                $after->role->value ?? (string) $after->role,
                $before->status,
                $after->status,
                $before->locked ? 'yes' : 'no',
                $after->locked ? 'yes' : 'no'
            )
        );
    }

    public function investmentCreated(User $actor, Investment $investment): void
    {
        $this->write(
            $actor,
            'investment-create',
            sprintf(
                'Created investment #%d %s with capital %.2f.',
                $investment->id,
                $investment->name,
                (float) $investment->capital_deployed
            )
        );
    }

    public function investmentUpdated(User $actor, Investment $before, Investment $after): void
    {
        $this->write(
            $actor,
            'investment-update',
            sprintf(
                'Updated investment #%d status %s->%s capital %.2f->%.2f.',
                $after->id,
                $before->status,
                $after->status,
                (float) $before->capital_deployed,
                (float) $after->capital_deployed
            )
        );
    }

    public function investmentDeleted(User $actor, Investment $investment): void
    {
        $this->write(
            $actor,
            'investment-delete',
            sprintf('Deleted investment #%d %s.', $investment->id, $investment->name)
        );
    }

    public function investmentMilestoneAdded(User $actor, Investment $investment, string $title): void
    {
        $this->write(
            $actor,
            'investment-milestone-add',
            sprintf('Added milestone "%s" to investment #%d.', $title, $investment->id)
        );
    }

    public function investmentMilestoneUpdated(User $actor, Investment $investment, string $title): void
    {
        $this->write(
            $actor,
            'investment-milestone-update',
            sprintf('Updated milestone "%s" on investment #%d.', $title, $investment->id)
        );
    }

    public function investmentMilestoneDeleted(User $actor, Investment $investment, string $title): void
    {
        $this->write(
            $actor,
            'investment-milestone-delete',
            sprintf('Deleted milestone "%s" from investment #%d.', $title, $investment->id)
        );
    }

    public function investmentCollectionAdded(User $actor, Investment $investment, float $profit): void
    {
        $this->write(
            $actor,
            'investment-collection-add',
            sprintf('Added collection entry to investment #%d with profit %.2f.', $investment->id, $profit)
        );
    }

    public function investmentCollectionUpdated(User $actor, Investment $investment, float $profit): void
    {
        $this->write(
            $actor,
            'investment-collection-update',
            sprintf('Updated collection entry on investment #%d with profit %.2f.', $investment->id, $profit)
        );
    }

    public function investmentCollectionDeleted(User $actor, Investment $investment, float $profit): void
    {
        $this->write(
            $actor,
            'investment-collection-delete',
            sprintf('Deleted collection entry from investment #%d with profit %.2f.', $investment->id, $profit)
        );
    }

    public function announcementCreated(User $actor, Announcement $announcement): void
    {
        $this->write(
            $actor,
            'announcement-create',
            sprintf('Created announcement #%d "%s".', $announcement->id, $announcement->title)
        );
    }

    public function announcementUpdated(User $actor, Announcement $before, Announcement $after): void
    {
        $this->write(
            $actor,
            'announcement-update',
            sprintf(
                'Updated announcement #%d pinned %s->%s.',
                $after->id,
                $before->pinned ? 'yes' : 'no',
                $after->pinned ? 'yes' : 'no'
            )
        );
    }

    public function announcementDeleted(User $actor, Announcement $announcement): void
    {
        $this->write(
            $actor,
            'announcement-delete',
            sprintf('Deleted announcement #%d "%s".', $announcement->id, $announcement->title)
        );
    }

    public function proposalCreated(User $actor, Proposal $proposal): void
    {
        $this->write(
            $actor,
            'proposal-create',
            sprintf('Created proposal #%d "%s".', $proposal->id, $proposal->title)
        );
    }

    public function proposalUpdated(User $actor, Proposal $proposal): void
    {
        $this->write(
            $actor,
            'proposal-update',
            sprintf('Updated proposal #%d "%s".', $proposal->id, $proposal->title)
        );
    }

    public function proposalStatusChanged(User $actor, Proposal $proposal, string $oldStatus): void
    {
        $this->write(
            $actor,
            'proposal-status',
            sprintf('Changed proposal #%d status %s->%s.', $proposal->id, $oldStatus, $proposal->status)
        );
    }

    public function proposalVoted(User $actor, Proposal $proposal, string $vote): void
    {
        $this->write(
            $actor,
            'proposal-vote',
            sprintf('Voted %s on proposal #%d.', $vote, $proposal->id)
        );
    }

    public function proposalFinalized(
        User $actor,
        Proposal $proposal,
        int $yesVotes,
        int $noVotes,
        int $quorum,
        bool $forced
    ): void {
        $this->write(
            $actor,
            'proposal-finalized',
            sprintf(
                'Finalized proposal #%d as %s (yes=%d, no=%d, quorum=%d, forced=%s).',
                $proposal->id,
                $proposal->status,
                $yesVotes,
                $noVotes,
                $quorum,
                $forced ? 'yes' : 'no'
            )
        );
    }

    public function documentCreated(User $actor, Document $document): void
    {
        $this->write(
            $actor,
            'document-create',
            sprintf('Added document #%d "%s" in %s.', $document->id, $document->name, $document->category)
        );
    }

    public function documentDeleted(User $actor, Document $document): void
    {
        $this->write(
            $actor,
            'document-delete',
            sprintf('Deleted document #%d "%s".', $document->id, $document->name)
        );
    }

    private function write(User $actor, string $action, string $detail): void
    {
        Activity::query()->create([
            'action' => $action,
            'detail' => $detail,
            'user_id' => $actor->id,
            'role' => $actor->role->value ?? (string) $actor->role,
        ]);
    }
}
