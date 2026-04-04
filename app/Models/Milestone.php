<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    protected $fillable = [
        'investment_id',
        'title',
        'note',
        'date',
        'done',
    ];

    protected $casts = [
        'date' => 'date',
        'done' => 'boolean',
    ];

    public function investment(): BelongsTo
    {
        return $this->belongsTo(Investment::class);
    }
}
