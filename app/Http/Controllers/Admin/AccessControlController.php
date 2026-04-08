<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\RoleOptionPermission;
use App\Models\User;
use App\Support\RoleAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccessControlController extends Controller
{
    public function index(Request $request): View
    {
        $roles = Role::cases();
        $optionDefinitions = RoleAccess::options();

        $roleOptionMap = [];
        foreach ($roles as $role) {
            $enabled = RoleAccess::enabledOptionsForRole($role->value);
            $roleOptionMap[$role->value] = array_fill_keys($enabled, true);
        }

        $users = User::query()
            ->orderBy('name')
            ->paginate(20, ['*'], 'users_page')
            ->withQueryString();

        return view('admin.access-control', [
            'users' => $users,
            'roles' => $roles,
            'options' => $optionDefinitions,
            'roleOptionMap' => $roleOptionMap,
        ]);
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        $roleValues = array_map(fn (Role $role) => $role->value, Role::cases());

        $payload = $request->validate([
            'role' => ['required', Rule::in($roleValues)],
        ]);

        if (($user->role->value ?? $user->role) === 'admin' && $payload['role'] !== 'admin') {
            $adminCount = User::query()->where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->withErrors(['role' => 'At least one admin must remain in the system.']);
            }
        }

        $user->update([
            'role' => $payload['role'],
        ]);

        return back()->with('status', 'User role updated successfully.');
    }

    public function updateRoleOptions(Request $request, string $role): RedirectResponse
    {
        $roleValues = array_map(fn (Role $case) => $case->value, Role::cases());

        abort_unless(in_array($role, $roleValues, true), 404);

        $optionKeys = array_keys(RoleAccess::options());

        $payload = $request->validate([
            'options' => ['array'],
            'options.*' => [Rule::in($optionKeys)],
        ]);

        $selected = $payload['options'] ?? [];
        $selected = RoleAccess::normalizeForRole($role, $selected);

        RoleOptionPermission::query()->where('role', $role)->delete();

        if ($selected !== []) {
            RoleOptionPermission::query()->insert(array_map(fn (string $option) => [
                'role' => $role,
                'option_key' => $option,
                'created_at' => now(),
                'updated_at' => now(),
            ], $selected));
        }

        return back()->with('status', ucfirst($role).' option access updated.');
    }
}
