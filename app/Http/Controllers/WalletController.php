<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Wallet::class);

        $currentUser = $request->user();
        $role = $currentUser->role->value ?? $currentUser->role;
        $canViewAllWallets = in_array($role, ['admin', 'finance', 'secretary', 'advisor'], true);

        $walletsQuery = Wallet::query()
            ->with('user')
            ->withSum('histories as total_credit', 'amount')
            ->orderByDesc('available');

        if (! $canViewAllWallets) {
            $walletsQuery->where('user_id', $currentUser->id);
        }

        $wallets = $walletsQuery
            ->paginate(20)
            ->withQueryString();

        $selectedUserId = $canViewAllWallets ? $request->integer('user_id') : $currentUser->id;
        $selectedUser = null;

        if ($selectedUserId > 0) {
            $selectedUserQuery = User::query();

            if (! $canViewAllWallets) {
                $selectedUserQuery->where('id', $currentUser->id);
            }

            $selectedUser = $selectedUserQuery->find($selectedUserId);
        }

        $recentHistory = collect();

        if ($selectedUser?->wallet) {
            $recentHistory = $selectedUser->wallet->histories()
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->limit(30)
                ->get();
        }

        $usersQuery = User::query()->orderBy('name');

        if (! $canViewAllWallets) {
            $usersQuery->where('id', $currentUser->id);
        }

        $users = $usersQuery->get(['id', 'name', 'member_id']);

        return view('app.wallets.index', [
            'wallets' => $wallets,
            'users' => $users,
            'selectedUser' => $selectedUser,
            'recentHistory' => $recentHistory,
            'canViewAllWallets' => $canViewAllWallets,
        ]);
    }
}
