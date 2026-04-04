<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberUpdateRequest;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::query()->orderBy('name');

        if ($request->filled('role')) {
            $query->where('role', $request->string('role')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('q')) {
            $needle = '%'.$request->string('q')->toString().'%';
            $query->where(function ($builder) use ($needle): void {
                $builder
                    ->where('name', 'like', $needle)
                    ->orWhere('email', 'like', $needle)
                    ->orWhere('member_id', 'like', $needle);
            });
        }

        return view('app.members.index', [
            'members' => $query->paginate(20)->withQueryString(),
            'roles' => ['admin', 'finance', 'secretary', 'member'],
            'statuses' => ['active', 'pending', 'removed'],
        ]);
    }

    public function update(
        MemberUpdateRequest $request,
        User $user,
        ActivityLogService $activityLogService
    ): RedirectResponse {
        $this->authorize('update', $user);

        $before = $user->replicate();
        $before->role = $user->role;
        $before->status = $user->status;
        $before->locked = $user->locked;

        $payload = $request->validated();
        $payload['locked'] = (bool) $payload['locked'];

        $user->update($payload);

        $activityLogService->memberUpdated($request->user(), $before, $user->fresh());

        return back()->with('status', 'Member updated.');
    }
}
