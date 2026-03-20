<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * LogEmployeeActivity — automatic audit trail for all employee admin actions.
 *
 * Every request made by an authenticated employee is logged to activity_logs
 * with full context: who, what, when, from where, and the outcome.
 *
 * Runs AFTER the response is built so no latency is added to the admin UI.
 * Uses DB::table() directly (no Eloquent overhead, fire-and-forget pattern).
 *
 * What it logs:
 *   • Employee ID, name, role
 *   • HTTP method + path + query string
 *   • Response status code
 *   • IP address + user agent
 *   • Request body summary (sensitive fields masked)
 *   • Subject record (extracted from route parameters)
 *
 * Apply to all employee/admin route groups:
 *   ->middleware(LogEmployeeActivity::class)
 */
class LogEmployeeActivity
{
    /**
     * Fields that must NEVER appear in logs — mask with *** instead.
     */
    private const MASKED_FIELDS = [
        'password', 'password_confirmation', 'current_password',
        'new_password', 'token', 'api_key', 'secret', 'card_number',
        'cvv', 'pin',
    ];

    /**
     * Paths to skip (health check, metrics, etc.)
     */
    private const SKIP_PATHS = [
        'api/up', 'api/health',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log if an employee is authenticated on this request
        $employee = $request->attributes->get('authenticated_employee');

        if (! $employee) {
            return $response;
        }

        // Skip noisy health-check paths
        foreach (self::SKIP_PATHS as $skip) {
            if (str_starts_with($request->path(), $skip)) {
                return $response;
            }
        }

        try {
            $this->writeLog($request, $response, $employee);
        } catch (\Throwable) {
            // Logging must never break the response
        }

        return $response;
    }

    private function writeLog(Request $request, Response $response, object $employee): void
    {
        $statusCode = $response->getStatusCode();

        // Build a sanitised summary of the request body
        $bodySnapshot = $this->maskSensitiveFields($request->except(['_token']));

        // Try to identify the subject from route parameters
        [$subjectType, $subjectId] = $this->resolveSubject($request);

        // Derive a human-readable action string from method + path
        $action = $this->deriveAction($request);

        DB::table('activity_logs')->insert([
            'actor_type'   => 'employee',
            'actor_id'     => $employee->id,
            'action'       => $action,
            'description'  => implode(' ', [
                strtoupper($request->method()),
                '/' . $request->path(),
                '→',
                $statusCode,
            ]),
            'subject_type' => $subjectType,
            'subject_id'   => $subjectId,
            'properties'   => json_encode([
                'method'      => $request->method(),
                'path'        => $request->path(),
                'query'       => $request->getQueryString(),
                'status_code' => $statusCode,
                'body'        => $bodySnapshot,
                'employee'    => [
                    'id'         => $employee->id,
                    'name'       => $employee->name,
                    'role'       => $employee->role,
                    'department' => $employee->department,
                ],
            ]),
            'ip_address'   => $request->ip(),
            'user_agent'   => substr((string) $request->userAgent(), 0, 500),
            'url'          => $request->fullUrl(),
            'method'       => $request->method(),
            'created_at'   => now(),
        ]);
    }

    /**
     * Replace values of sensitive fields with "***".
     */
    private function maskSensitiveFields(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), self::MASKED_FIELDS, true)) {
                $data[$key] = '***';
            } elseif (is_array($value)) {
                $data[$key] = $this->maskSensitiveFields($value);
            }
        }

        return $data;
    }

    /**
     * Extract subject type/id from common route parameter names.
     * e.g.  /admin/users/{userId}  →  ('user', 42)
     */
    private function resolveSubject(Request $request): array
    {
        $paramMap = [
            'userId'        => 'App\\Models\\User',
            'user'          => 'App\\Models\\User',
            'id'            => null,  // determined by path segment
            'txnId'         => 'App\\Models\\RechargeTransaction',
            'transactionId' => 'App\\Models\\RechargeTransaction',
            'complaintId'   => 'App\\Models\\Complaint',
            'walletId'      => 'App\\Models\\Wallet',
            'employeeId'    => 'App\\Models\\Employee',
        ];

        foreach ($paramMap as $param => $model) {
            $value = $request->route($param);
            if ($value) {
                // If model is null, guess from path
                if ($model === null) {
                    $segment = explode('/', $request->path())[count(explode('/', $request->path())) - 2] ?? '';
                    $model = 'App\\Models\\' . ucfirst(rtrim($segment, 's'));
                }
                return [$model, is_numeric($value) ? (int) $value : null];
            }
        }

        return [null, null];
    }

    /**
     * Map HTTP method + path to a dot-notation action string.
     * e.g.  POST /admin/reports/recharges → employee.report.recharges.view
     */
    private function deriveAction(Request $request): string
    {
        $method = strtoupper($request->method());
        $path   = trim($request->path(), '/');

        $verbMap = [
            'GET'    => 'view',
            'POST'   => 'create',
            'PUT'    => 'update',
            'PATCH'  => 'update',
            'DELETE' => 'delete',
        ];

        $verb    = $verbMap[$method] ?? strtolower($method);
        $segment = str_replace(['api/v1/admin/', 'api/v1/'], '', $path);
        $segment = preg_replace('/\/\d+/', '', $segment); // strip IDs
        $segment = str_replace('/', '.', $segment);

        return "employee.{$segment}.{$verb}";
    }
}
