<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;

class ApiRequestLogSchema
{
    private static ?array $columns = null;

    public static function has(string $column): bool
    {
        return in_array($column, self::columns(), true);
    }

    public static function columns(): array
    {
        if (self::$columns !== null) {
            return self::$columns;
        }

        if (! Schema::hasTable('api_request_logs')) {
            return self::$columns = [];
        }

        return self::$columns = Schema::getColumnListing('api_request_logs');
    }
}
