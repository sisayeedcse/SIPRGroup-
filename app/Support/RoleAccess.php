<?php

namespace App\Support;

use App\Enums\Role;
use App\Models\RoleOptionPermission;

class RoleAccess
{
    public static function options(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'transactions' => 'Transactions',
            'wallets' => 'Wallets',
            'investments' => 'Investments',
            'members' => 'Members',
            'noticeboard' => 'Noticeboard',
            'documents' => 'Documents',
            'reports' => 'Reports',
            'activities' => 'Activity Log',
            'notifications' => 'Notifications',
            'approvals' => 'Pending Approvals',
            'access_control' => 'Access Control',
        ];
    }

    public static function roles(): array
    {
        return array_map(fn (Role $role) => $role->value, Role::cases());
    }

    public static function allows(string $role, string $option): bool
    {
        return in_array($option, self::enabledOptionsForRole($role), true);
    }

    public static function enabledOptionsForRole(string $role): array
    {
        $configured = RoleOptionPermission::query()
            ->where('role', $role)
            ->pluck('option_key')
            ->all();

        if ($configured === []) {
            return self::defaultEnabledOptionsForRole($role);
        }

        return array_values(array_intersect(array_keys(self::options()), $configured));
    }

    public static function defaultEnabledOptionsForRole(string $role): array
    {
        return match ($role) {
            'admin' => array_keys(self::options()),
            'finance' => [
                'dashboard', 'transactions', 'wallets', 'investments', 'members',
                'noticeboard', 'documents', 'reports', 'activities', 'notifications',
            ],
            'secretary' => [
                'dashboard', 'transactions', 'wallets', 'investments', 'members',
                'noticeboard', 'documents', 'reports', 'activities', 'notifications',
            ],
            'advisor' => [
                'dashboard', 'transactions', 'wallets', 'investments', 'members',
                'noticeboard', 'documents', 'reports', 'activities', 'notifications',
            ],
            default => [
                'dashboard', 'transactions', 'wallets', 'investments', 'members',
                'noticeboard', 'documents', 'notifications',
            ],
        };
    }

    public static function normalizeForRole(string $role, array $selected): array
    {
        $validOptions = array_keys(self::options());
        $normalized = array_values(array_unique(array_intersect($selected, $validOptions)));

        if ($role === 'admin') {
            foreach (['approvals', 'access_control'] as $required) {
                if (! in_array($required, $normalized, true)) {
                    $normalized[] = $required;
                }
            }
        }

        return $normalized;
    }
}
