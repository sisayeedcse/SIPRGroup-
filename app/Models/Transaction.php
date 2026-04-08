<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'adjustment_for_id',
        'type',
        'user_id',
        'amount',
        'date',
        'note',
        'is_adjustment',
        'requires_approval',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_note',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'is_adjustment' => 'boolean',
        'requires_approval' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adjustedTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'adjustment_for_id');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(self::class, 'adjustment_for_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
