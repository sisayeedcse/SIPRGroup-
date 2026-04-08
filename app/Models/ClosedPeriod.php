<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClosedPeriod extends Model
{
    protected $fillable = [
        'month',
        'closed_by',
        'closed_at',
        'note',
    ];

    protected $casts = [
        'month' => 'date',
        'closed_at' => 'datetime',
    ];

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
