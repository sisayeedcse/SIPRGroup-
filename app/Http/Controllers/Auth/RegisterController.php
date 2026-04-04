<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingApproval;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'invite_code' => ['required', 'string', 'max:50'],
        ]);

        $inviteCode = strtoupper(trim($data['invite_code']));

        $member = User::query()->where('member_id', $inviteCode)->first();

        if (! $member) {
            return back()
                ->withErrors(['invite_code' => 'Invalid invite code.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }

        if ($this->isAlreadyClaimed($member)) {
            return back()
                ->withErrors(['invite_code' => 'This invite code is already used.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }

        $member->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'pending',
        ])->save();

        PendingApproval::query()->firstOrCreate(
            ['user_id' => $member->id, 'status' => 'pending'],
            ['user_id' => $member->id, 'status' => 'pending']
        );

        return redirect()
            ->route('login')
            ->with('status', 'Registration submitted. Wait for admin approval.');
    }

    private function isAlreadyClaimed(User $user): bool
    {
        return (bool) $user->google_id || ! str_ends_with(strtolower($user->email), '@sipr.com');
    }
}
