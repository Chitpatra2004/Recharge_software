<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(
        string  $action,
        ?string $description   = null,
        ?Model  $subject       = null,
        array   $properties    = [],
        ?int    $userId        = null,
        ?Request $request      = null
    ): ActivityLog {
        $req = $request ?? request();

        return ActivityLog::create([
            'user_id'      => $userId ?? Auth::id(),
            'action'       => $action,
            'description'  => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->getKey(),
            'properties'   => $properties ?: null,
            'ip_address'   => $req->ip(),
            'user_agent'   => $req->userAgent(),
            'url'          => $req->fullUrl(),
            'method'       => $req->method(),
            'created_at'   => now(),
        ]);
    }
}
