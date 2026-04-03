<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id','email','ip_address','user_agent','event','success',
    ];

    protected $casts = ['success' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }

    public static function record(string $event, string $email, ?int $userId, bool $success, $request): void
    {
        static::create([
            'user_id'    => $userId,
            'email'      => $email,
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'event'      => $event,
            'success'    => $success,
            'created_at' => now(),
        ]);
    }
}
