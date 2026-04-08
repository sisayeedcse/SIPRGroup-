<?php

namespace App\Http\Middleware;

use App\Support\RoleAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessOption
{
    public function handle(Request $request, Closure $next, string $option): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $role = $user->role->value ?? $user->role;

        if (! RoleAccess::allows($role, $option)) {
            abort(403, 'You are not authorized for this option.');
        }

        return $next($request);
    }
}
