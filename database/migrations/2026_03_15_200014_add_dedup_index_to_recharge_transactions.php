<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a composite index that makes the 60-second duplicate-prevention
 * query in RechargeService::assertNoDuplicateInWindow() fast at scale.
 *
 * Query being optimised:
 *   SELECT * FROM recharge_transactions
 *   WHERE  user_id       = ?
 *     AND  mobile        = ?
 *     AND  operator_code = ?
 *     AND  recharge_type = ?
 *     AND  amount        = ?
 *     AND  status NOT IN ('failed', 'refunded')
 *     AND  created_at   >= NOW() - INTERVAL 60 SECOND
 *   LIMIT 1;
 *
 * Index column order rationale (highest selectivity first):
 *   user_id      — typically filters to 1–10 rows per second per user
 *   mobile       — narrows to a single subscriber
 *   operator_code — narrows by operator
 *   recharge_type — low cardinality but still narrows
 *   created_at   — range scan on the already-narrow result set
 *
 * The `status` filter has low cardinality (7 values) and is applied as a
 * post-index filter — adding it to the index would inflate key length without
 * meaningful benefit at this selectivity.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recharge_transactions', function (Blueprint $table) {
            $table->index(
                ['user_id', 'mobile', 'operator_code', 'recharge_type', 'created_at'],
                'idx_rt_dedup_window'
            );
        });
    }

    public function down(): void
    {
        Schema::table('recharge_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_rt_dedup_window');
        });
    }
};
