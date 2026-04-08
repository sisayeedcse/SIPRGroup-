<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyDue extends Model
{
    protected $fillable = [
        'user_id',
        'due_month',
        'expected_amount',
        'paid_amount',
        'status',
    ];

    protected $casts = [
        'due_month' => 'date',
        'expected_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
