<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerPaymentRequest extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'payment_mode', 'reference_number',
        'proof_image', 'notes', 'status', 'admin_notes', 'processed_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
}
