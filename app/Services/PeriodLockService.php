<?php

namespace App\Services;

use App\Models\ClosedPeriod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class PeriodLockService
{
    public function isMonthClosed(string $date): bool
    {
        $month = Carbon::parse($date)->startOfMonth()->toDateString();

        return ClosedPeriod::query()->whereDate('month', $month)->exists();
    }

    public function closeMonth(string $month, int $closedBy, ?string $note = null): ClosedPeriod
    {
        $monthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();

        return ClosedPeriod::query()->updateOrCreate(
            ['month' => $monthDate],
            [
                'closed_by' => $closedBy,
                'closed_at' => now(),
                'note' => $note,
            ]
        );
    }

    public function ensureWritableForActor(User $actor, string $date): void
    {
        $role = $actor->role->value ?? $actor->role;

        if ($role === 'admin') {
            return;
        }

        if ($this->isMonthClosed($date)) {
            throw ValidationException::withMessages([
                'date' => 'This month is closed. Please contact admin for override.',
            ]);
        }
    }
}
