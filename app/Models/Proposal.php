<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $fillable = [
        'title',
        'description',
        'amount',
        'date',
        'proposed_by',
        'status',
        'quorum_required',
        'closes_at',
        'finalized_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'closes_at' => 'date',
        'finalized_at' => 'datetime',
    ];

    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ProposalVote::class);
    }
}
