<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class WalletHistory extends Model
{
    protected $fillable = [
        'wallet_id',
        'date',
        'type',
        'label',
        'amount',
        'note',
        'is_locked',
        'unlock_date',
    ];

    protected $casts = [
        'date' => 'date',
        'unlock_date' => 'date',
        'amount' => 'decimal:2',
        'is_locked' => 'boolean',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
