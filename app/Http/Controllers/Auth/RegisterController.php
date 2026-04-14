<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingApproval;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
        ]);

        DB::transaction(function () use ($data): void {
            $user = User::query()->create([
                'member_id' => $this->generateMemberId($data['name']),
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'member',
                'status' => 'pending',
                'locked' => false,
            ]);

            PendingApproval::query()->create([
                'user_id' => $user->id,
                'status' => 'pending',
            ]);
        });

        return redirect()
            ->route('login')
            ->with('status', 'Registration submitted. Your account will be activated after admin approval.');
    }

    private function generateMemberId(string $name): string
    {
        $prefix = Str::of($name)
            ->upper()
            ->replaceMatches('/[^A-Z ]/', '')
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::substr($part, 0, 1))
            ->implode('');

        if ($prefix === '') {
            $prefix = 'MB';
        }

        do {
            $memberId = sprintf('SIPR%s-%s-%04d', now()->format('y'), Str::padRight($prefix, 2, 'X'), random_int(0, 9999));
        } while (User::query()->where('member_id', $memberId)->exists());

        return $memberId;
    }
}
