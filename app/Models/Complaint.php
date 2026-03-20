<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'recharge_transaction_id', 'ticket_number',
        'subject', 'description', 'type', 'status', 'priority',
        'assigned_to', 'resolved_at',
    ];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(RechargeTransaction::class, 'recharge_transaction_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ComplaintLog::class);
    }
}
