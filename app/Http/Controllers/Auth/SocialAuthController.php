<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingApproval;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('login')->withErrors(['email' => 'Google login failed.']);
        }

        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->orWhere('google_email', $googleUser->getEmail())
            ->first();

        if ($user) {
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'google_email' => $googleUser->getEmail(),
            ])->save();

            if ($user->status !== 'active') {
                return redirect()->route('login')->withErrors(['email' => 'Your account is pending admin approval.']);
            }

            Auth::login($user, true);

            return redirect()->route('dashboard');
        }

        session([
            'social_profile' => [
                'id' => $googleUser->getId(),
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
            ],
        ]);

        return redirect()->route('auth.invite.link.show');
    }

    public function showInviteLinkForm(Request $request)
    {
        abort_unless($request->session()->has('social_profile'), 403);

        return view('auth.link-invite', [
            'profile' => $request->session()->get('social_profile'),
        ]);
    }

    public function linkInvite(Request $request): RedirectResponse
    {
        $request->validate([
            'invite_code' => ['required', 'string', 'max:50'],
        ]);

        $profile = $request->session()->get('social_profile');
        abort_unless($profile, 403);

        $inviteCode = strtoupper(trim((string) $request->input('invite_code')));

        $member = User::query()->where('member_id', $inviteCode)->first();

        if (! $member) {
            return back()->withErrors(['invite_code' => 'Invalid invite code.']);
        }

        if ($member->google_id && $member->google_id !== $profile['id']) {
            return back()->withErrors(['invite_code' => 'This invite code is already linked.']);
        }

        $member->forceFill([
            'name' => $member->name ?: $profile['name'],
            'google_id' => $profile['id'],
            'google_email' => $profile['email'],
            'password' => $member->password ?: Hash::make(Str::random(32)),
            'status' => $member->status === 'active' ? 'active' : 'pending',
        ])->save();

        if ($member->status !== 'active') {
            PendingApproval::query()->firstOrCreate(
                ['user_id' => $member->id, 'status' => 'pending'],
                ['user_id' => $member->id, 'status' => 'pending']
            );
        }

        $request->session()->forget('social_profile');

        if ($member->status === 'active') {
            Auth::login($member, true);

            return redirect()->route('dashboard');
        }

        return redirect()
            ->route('login')
            ->with('status', 'Account linked. Wait for admin approval.');
    }
}
