<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerIntegrationRequest extends Model
{
    protected $fillable = [
        'user_id', 'api_name',
        'low_balance_notification', 'low_balance_limit', 'notification_types',
        'website_url', 'callback_url', 'status_check_url', 'dispute_url',
        'site_username', 'site_password_hint',
        'allowed_ips',
        'recharge_api', 'status_api', 'balance_api', 'dispute_api', 'callback_config', 'op_code_map',
        'status', 'api_status', 'admin_status', 'admin_notes', 'approved_at', 'rejected_at',
    ];

    protected $casts = [
        'approved_at'              => 'datetime',
        'rejected_at'              => 'datetime',
        'low_balance_notification' => 'boolean',
        'low_balance_limit'        => 'decimal:2',
        'notification_types'       => 'array',
        'recharge_api'             => 'array',
        'status_api'               => 'array',
        'balance_api'              => 'array',
        'dispute_api'              => 'array',
        'callback_config'          => 'array',
        'op_code_map'              => 'array',
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
