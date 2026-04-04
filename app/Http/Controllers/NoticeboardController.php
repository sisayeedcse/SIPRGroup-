<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Http\Requests\ProposalRequest;
use App\Http\Requests\ProposalVoteRequest;
use App\Models\Announcement;
use App\Models\Proposal;
use App\Models\ProposalVote;
use App\Services\ActivityLogService;
use App\Services\ProposalGovernanceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoticeboardController extends Controller
{
    public function showProposal(Proposal $proposal): View
    {
        $this->authorize('view', $proposal);

        $proposal->load([
            'proposer',
            'votes.user',
        ]);

        $proposal->loadCount([
            'votes as yes_votes_count' => fn ($q) => $q->where('vote', 'yes'),
            'votes as no_votes_count' => fn ($q) => $q->where('vote', 'no'),
        ]);

        $myVote = null;

        if (auth()->check()) {
            $myVote = $proposal->votes->firstWhere('user_id', auth()->id())?->vote;
        }

        return view('app.noticeboard.show', [
            'proposal' => $proposal,
            'myVote' => $myVote,
        ]);
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Announcement::class);
        $this->authorize('viewAny', Proposal::class);

        $announcements = Announcement::query()
            ->with('author')
            ->orderByDesc('pinned')
            ->latest()
            ->paginate(8, ['*'], 'announcements_page')
            ->withQueryString();

        $proposals = Proposal::query()
            ->with(['proposer', 'votes'])
            ->withCount([
                'votes as yes_votes_count' => fn ($q) => $q->where('vote', 'yes'),
                'votes as no_votes_count' => fn ($q) => $q->where('vote', 'no'),
            ])
            ->latest('closes_at')
            ->latest('id')
            ->paginate(8, ['*'], 'proposals_page')
            ->withQueryString();

        $myVotes = ProposalVote::query()
            ->where('user_id', $request->user()->id)
            ->pluck('vote', 'proposal_id');

        return view('app.noticeboard.index', [
            'announcements' => $announcements,
            'proposals' => $proposals,
            'myVotes' => $myVotes,
        ]);
    }

    public function storeAnnouncement(
        AnnouncementRequest $request,
        ActivityLogService $activityLogService
    ): RedirectResponse {
        $this->authorize('create', Announcement::class);

        $payload = $request->validated();
        $payload['author_id'] = $request->user()->id;
        $payload['pinned'] = (bool) ($payload['pinned'] ?? false);

        $announcement = Announcement::query()->create($payload);
        $activityLogService->announcementCreated($request->user(), $announcement);

        return back()->with('status', 'Announcement created.');
    }

    public function updateAnnouncement(
        AnnouncementRequest $request,
        Announcement $announcement,
        ActivityLogService $activityLogService
    ): RedirectResponse {
        $this->authorize('update', $announcement);

        $before = $announcement->replicate();
        $before->pinned = $announcement->pinned;

        $payload = $request->validated();
        $payload['pinned'] = (bool) ($payload['pinned'] ?? false);

        $announcement->update($payload);
        $activityLogService->announcementUpdated($request->user(), $before, $announcement->fresh());

        return back()->with('status', 'Announcement updated.');
    }

    public function destroyAnnouncement(
        Announcement $announcement,
        ActivityLogService $activityLogService,
        Request $request
    ): RedirectResponse {
        $this->authorize('delete', $announcement);

        $activityLogService->announcementDeleted($request->user(), $announcement);
        $announcement->delete();

        return back()->with('status', 'Announcement deleted.');
    }

    public function storeProposal(ProposalRequest $request, ActivityLogService $activityLogService): RedirectResponse
    {
        $this->authorize('create', Proposal::class);

        $payload = $request->validated();
        $payload['proposed_by'] = $request->user()->id;
        $payload['status'] = 'active';
        $payload['quorum_required'] = (int) ($payload['quorum_required'] ?? 3);
        $payload['closes_at'] = $payload['closes_at'] ?? now()->addDays(7)->toDateString();

        $proposal = Proposal::query()->create($payload);
        $activityLogService->proposalCreated($request->user(), $proposal);

        return back()->with('status', 'Proposal submitted.');
    }

    public function updateProposal(
        ProposalRequest $request,
        Proposal $proposal,
        ActivityLogService $activityLogService
    ): RedirectResponse {
        $this->authorize('update', $proposal);

        $payload = $request->validated();
        unset($payload['status']);

        $proposal->update($payload);
        $activityLogService->proposalUpdated($request->user(), $proposal->fresh());

        return back()->with('status', 'Proposal updated.');
    }

    public function updateProposalStatus(
        Proposal $proposal,
        Request $request,
        ActivityLogService $activityLogService
    ): RedirectResponse {
        $this->authorize('updateStatus', $proposal);

        $request->validate([
            'status' => ['required', 'in:active,approved,rejected'],
        ]);

        $old = $proposal->status;
        $proposal->update([
            'status' => $request->string('status')->toString(),
        ]);

        $activityLogService->proposalStatusChanged($request->user(), $proposal, $old);

        return back()->with('status', 'Proposal status updated.');
    }

    public function vote(
        ProposalVoteRequest $request,
        Proposal $proposal,
        ActivityLogService $activityLogService
    ): RedirectResponse {
        $this->authorize('vote', $proposal);

        if ($proposal->status !== 'active' || $proposal->finalized_at !== null) {
            return back()->withErrors(['vote' => 'This proposal is already finalized.']);
        }

        if ($proposal->closes_at && Carbon::parse((string) $proposal->closes_at)->isPast()) {
            return back()->withErrors(['vote' => 'Voting window is closed.']);
        }

        $vote = ProposalVote::query()->updateOrCreate(
            [
                'proposal_id' => $proposal->id,
                'user_id' => $request->user()->id,
            ],
            [
                'vote' => $request->string('vote')->toString(),
            ]
        );

        $activityLogService->proposalVoted($request->user(), $proposal, $vote->vote);

        return back()->with('status', 'Vote submitted.');
    }

    public function finalizeProposal(
        Proposal $proposal,
        Request $request,
        ProposalGovernanceService $governanceService
    ): RedirectResponse {
        $this->authorize('finalize', $proposal);

        if (! $governanceService->finalizeOne($proposal, $request->user(), true)) {
            return back()->withErrors(['proposal' => 'Proposal could not be finalized.']);
        }

        return back()->with('status', 'Proposal finalized.');
    }
}
