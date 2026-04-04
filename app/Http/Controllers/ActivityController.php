<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewActivityLog');

        $query = Activity::query()->with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->string('action')->toString());
        }

        if ($request->filled('role')) {
            $query->where('role', $request->string('role')->toString());
        }

        if ($request->filled('q')) {
            $needle = '%'.$request->string('q')->toString().'%';
            $query->where(function ($builder) use ($needle): void {
                $builder
                    ->where('detail', 'like', $needle)
                    ->orWhereHas('user', function ($userQuery) use ($needle): void {
                        $userQuery
                            ->where('name', 'like', $needle)
                            ->orWhere('member_id', 'like', $needle);
                    });
            });
        }

        return view('app.activities.index', [
            'activities' => $query->paginate(25)->withQueryString(),
            'actions' => [
                'tx-create',
                'tx-update',
                'tx-delete',
                'member-update',
                'investment-create',
                'investment-update',
                'investment-delete',
                'investment-milestone-add',
                'investment-collection-add',
                'announcement-create',
                'announcement-update',
                'announcement-delete',
                'proposal-create',
                'proposal-update',
                'proposal-status',
                'proposal-vote',
                'proposal-finalized',
                'document-create',
                'document-delete',
            ],
            'roles' => ['admin', 'finance', 'secretary', 'member'],
        ]);
    }
}