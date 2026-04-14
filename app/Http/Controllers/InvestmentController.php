<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvestmentCollectionRequest;
use App\Http\Requests\InvestmentMilestoneRequest;
use App\Http\Requests\InvestmentRequest;
use App\Models\Collection as InvestmentCollection;
use App\Models\Milestone;
use App\Models\Investment;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvestmentController extends Controller
{
    public function show(Investment $investment): View
    {
        $this->authorize('view', $investment);

        $investment->load([
            'milestones' => fn ($q) => $q->orderByDesc('date')->orderByDesc('id'),
            'collections' => fn ($q) => $q->orderByDesc('date')->orderByDesc('id'),
        ]);

        return view('app.investments.show', [
            'investment' => $investment,
        ]);
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Investment::class);

        $query = Investment::query()->orderByDesc('date')->orderByDesc('id');

        $status = $request->string('status')->toString();
        if ($status === '') {
            $status = 'active';
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($request->filled('q')) {
            $needle = '%'.$request->string('q')->toString().'%';
            $query->where(function ($builder) use ($needle): void {
                $builder
                    ->where('name', 'like', $needle)
                    ->orWhere('sector', 'like', $needle)
                    ->orWhere('partner', 'like', $needle);
            });
        }

        $summaryQuery = clone $query;

        $investments = $query->paginate(12)->withQueryString();
        $summary = [
            'deployed' => (float) $summaryQuery->sum('capital_deployed'),
            'returned' => (float) $summaryQuery->sum('actual_return'),
            'active' => (clone $summaryQuery)->where('status', 'active')->count(),
        ];
        $selected = null;

        if ($request->filled('investment')) {
            $selected = Investment::query()
                ->with([
                    'milestones' => fn ($q) => $q->orderByDesc('date')->orderByDesc('id'),
                    'collections' => fn ($q) => $q->orderByDesc('date')->orderByDesc('id'),
                ])
                ->find($request->integer('investment'));
        }

        return view('app.investments.index', [
            'investments' => $investments,
            'selected' => $selected,
            'summary' => $summary,
            'selectedStatus' => $status,
            'statuses' => ['active', 'completed', 'paused'],
        ]);
    }

    public function store(InvestmentRequest $request, ActivityLogService $activityLogService): RedirectResponse
    {
        $this->authorize('create', Investment::class);

        $payload = $request->validated();
        $payload['actual_return'] = $payload['actual_return'] ?? 0;

        $investment = Investment::query()->create($payload);
        $activityLogService->investmentCreated($request->user(), $investment);

        return back()->with('status', 'Investment created.');
    }

    public function update(
        InvestmentRequest $request,
        Investment $investment,
        ActivityLogService $activityLogService
    ): RedirectResponse {
        $this->authorize('update', $investment);

        $before = $investment->replicate();
        $before->status = $investment->status;
        $before->capital_deployed = $investment->capital_deployed;

        $payload = $request->validated();
        $payload['actual_return'] = $payload['actual_return'] ?? ($investment->actual_return ?? 0);

        $investment->update($payload);
        $activityLogService->investmentUpdated($request->user(), $before, $investment->fresh());

        return back()->with('status', 'Investment updated.');
    }

    public function destroy(
        Investment $investment,
        ActivityLogService $activityLogService,
        Request $request
    ): RedirectResponse {
        $this->authorize('delete', $investment);

        $activityLogService->investmentDeleted($request->user(), $investment);
        $investment->delete();

        return back()->with('status', 'Investment deleted.');
    }

    public function storeMilestone(
        InvestmentMilestoneRequest $request,
        Investment $investment,
        ActivityLogService $activityLogService
    ): RedirectResponse {
        $this->authorize('createMilestone', $investment);

        $payload = $request->validated();
        $payload['done'] = (bool) ($payload['done'] ?? false);

        $milestone = $investment->milestones()->create($payload);
        $activityLogService->investmentMilestoneAdded($request->user(), $investment, $milestone->title);

        return back()->with('status', 'Milestone added.');
    }

    public function updateMilestone(
        InvestmentMilestoneRequest $request,
        Investment $investment,
        Milestone $milestone,
        ActivityLogService $activityLogService
    ): RedirectResponse {
        $this->authorize('update', $investment);
        abort_unless($milestone->investment_id === $investment->id, 404);

        $payload = $request->validated();
        $payload['done'] = (bool) ($payload['done'] ?? false);

        $milestone->update($payload);
        $activityLogService->investmentMilestoneUpdated($request->user(), $investment, $milestone->title);

        return back()->with('status', 'Milestone updated.');
    }

    public function destroyMilestone(
        Investment $investment,
        Milestone $milestone,
        ActivityLogService $activityLogService,
        Request $request
    ): RedirectResponse {
        $this->authorize('delete', $investment);
        abort_unless($milestone->investment_id === $investment->id, 404);

        $activityLogService->investmentMilestoneDeleted($request->user(), $investment, $milestone->title);
        $milestone->delete();

        return back()->with('status', 'Milestone deleted.');
    }

    public function storeCollection(
        InvestmentCollectionRequest $request,
        Investment $investment,
        ActivityLogService $activityLogService
    ): RedirectResponse {
        $this->authorize('createCollection', $investment);

        $payload = $request->validated();
        $payload['kg'] = $payload['kg'] ?? 0;
        $payload['sold_kg'] = $payload['sold_kg'] ?? 0;
        $payload['revenue'] = $payload['revenue'] ?? 0;
        $payload['cost'] = $payload['cost'] ?? 0;
        $payload['profit'] = $payload['profit'] ?? ((float) $payload['revenue'] - (float) $payload['cost']);

        $collection = $investment->collections()->create($payload);
        $activityLogService->investmentCollectionAdded($request->user(), $investment, (float) $collection->profit);

        return back()->with('status', 'Collection entry added.');
    }

    public function updateCollection(
        InvestmentCollectionRequest $request,
        Investment $investment,
        InvestmentCollection $collection,
        ActivityLogService $activityLogService
    ): RedirectResponse {
        $this->authorize('update', $investment);
        abort_unless($collection->investment_id === $investment->id, 404);

        $payload = $request->validated();
        $payload['kg'] = $payload['kg'] ?? 0;
        $payload['sold_kg'] = $payload['sold_kg'] ?? 0;
        $payload['revenue'] = $payload['revenue'] ?? 0;
        $payload['cost'] = $payload['cost'] ?? 0;
        $payload['profit'] = $payload['profit'] ?? ((float) $payload['revenue'] - (float) $payload['cost']);

        $collection->update($payload);
        $activityLogService->investmentCollectionUpdated($request->user(), $investment, (float) $collection->profit);

        return back()->with('status', 'Collection entry updated.');
    }

    public function destroyCollection(
        Investment $investment,
        InvestmentCollection $collection,
        ActivityLogService $activityLogService,
        Request $request
    ): RedirectResponse {
        $this->authorize('delete', $investment);
        abort_unless($collection->investment_id === $investment->id, 404);

        $activityLogService->investmentCollectionDeleted($request->user(), $investment, (float) $collection->profit);
        $collection->delete();

        return back()->with('status', 'Collection entry deleted.');
    }
}
