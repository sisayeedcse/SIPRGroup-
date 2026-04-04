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

        $wallets = Wallet::query()
            ->with('user')
            ->withSum('histories as total_credit', 'amount')
            ->orderByDesc('available')
            ->paginate(20)
            ->withQueryString();

        $selectedUserId = $request->integer('user_id');
        $selectedUser = null;

        if ($selectedUserId > 0) {
            $selectedUser = User::query()->find($selectedUserId);
        }

        $recentHistory = collect();

        if ($selectedUser?->wallet) {
            $recentHistory = $selectedUser->wallet->histories()
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->limit(30)
                ->get();
        }

        $users = User::query()->orderBy('name')->get(['id', 'name', 'member_id']);

        return view('app.wallets.index', [
            'wallets' => $wallets,
            'users' => $users,
            'selectedUser' => $selectedUser,
            'recentHistory' => $recentHistory,
        ]);
    }
}
