<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PendingApproval;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function index(): View
    {
        $pending = PendingApproval::query()
            ->where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();

        return view('admin.pending-approvals', [
            'pending' => $pending,
        ]);
    }

    public function approve(PendingApproval $approval): RedirectResponse
    {
        $approval->update(['status' => 'approved']);
        $approval->user?->update(['status' => 'active']);

        return back()->with('status', 'Registration approved.');
    }

    public function reject(PendingApproval $approval): RedirectResponse
    {
        $approval->update(['status' => 'rejected']);
        $approval->user?->update(['status' => 'removed']);

        return back()->with('status', 'Registration rejected.');
    }
}
