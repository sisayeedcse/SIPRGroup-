<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = [
        'investment_id',
        'date',
        'kg',
        'type',
        'source',
        'sold_kg',
        'revenue',
        'cost',
        'profit',
    ];

    protected $casts = [
        'date' => 'date',
        'kg' => 'decimal:2',
        'sold_kg' => 'decimal:2',
        'revenue' => 'decimal:2',
        'cost' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }
}
