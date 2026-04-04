<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->status !== 'active') {
            Auth::logout();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Your account is not active yet. Please contact admin.']);
        }

        return $next($request);
    }
}
