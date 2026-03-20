<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerIntegrationRequest extends Model
{
    protected $fillable = [
        'user_id', 'website_url', 'callback_url',
        'site_username', 'site_password_hint',
        'status', 'admin_notes', 'approved_at', 'rejected_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
}
