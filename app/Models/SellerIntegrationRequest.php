<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerIntegrationRequest extends Model
{
    protected $fillable = [
        'user_id', 'website_url', 'callback_url', 'status_check_url', 'dispute_url',
        'site_username', 'site_password_hint',
        'allowed_ips',
        'status', 'api_status', 'admin_status', 'admin_notes', 'approved_at', 'rejected_at',
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
    public function isApiEnabled(): bool { return $this->api_status === 'enabled'; }
    public function isAdminEnabled(): bool { return $this->admin_status === 'enabled'; }
}
