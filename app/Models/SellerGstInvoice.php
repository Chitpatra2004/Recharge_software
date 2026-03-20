<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerGstInvoice extends Model
{
    protected $fillable = [
        'user_id', 'invoice_number', 'invoice_date',
        'amount', 'gst_amount', 'file_path', 'period_from', 'period_to',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'period_from'  => 'date',
        'period_to'    => 'date',
        'amount'       => 'decimal:2',
        'gst_amount'   => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
