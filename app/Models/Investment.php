<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    protected $fillable = [
        'name',
        'description',
        'sector',
        'partner',
        'date',
        'capital_deployed',
        'expected_return',
        'actual_return',
        'status',
        'team_members',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'capital_deployed' => 'decimal:2',
        'expected_return' => 'decimal:2',
        'actual_return' => 'decimal:2',
        'team_members' => 'array',
    ];

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }
}
