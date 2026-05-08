<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPaymentRequest extends Model
{
    protected $fillable = [
        'user_id','amount','payment_mode','reference_number',
        'upi_id','payment_date','notes','proof_image',
        'status','admin_notes','processed_at','processed_by_employee_id',
    ];

    protected $casts = [
        'amount'       => 'float',
        'payment_date' => 'date',
        'processed_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public function processor()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'processed_by_employee_id');
    }
}
