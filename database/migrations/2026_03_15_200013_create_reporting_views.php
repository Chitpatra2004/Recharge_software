<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * ╔══════════════════════════════════════════════════════════════════════╗
 * ║  REPORTING VIEWS                                                      ║
 * ║                                                                       ║
 * ║  MySQL views that pre-join common reporting query patterns.          ║
 * ║  These views do NOT add I/O overhead — they are resolved at query    ║
 * ║  time. They exist to:                                                ║
 * ║   1. Standardize reporting query access for the team                 ║
 * ║   2. Allow index-driven query plans to work transparently            ║
 * ║   3. Decouple reporting code from schema changes                     ║
 * ║                                                                       ║
 * ║  For heavy aggregations (daily summary), use the                     ║
 * ║  RechargeReportingService which writes to summary tables instead.    ║
 * ╚══════════════════════════════════════════════════════════════════════╝
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── View 1: v_recharge_summary ──────────────────────────────────
        // Used by: admin dashboard, daily report cron
        // Pattern: GROUP BY user + operator + date
        DB::statement("
            CREATE OR REPLACE VIEW v_recharge_summary AS
            SELECT
                rt.user_id,
                u.name               AS user_name,
                u.role               AS user_role,
                rt.operator_code,
                rt.recharge_type,
                rt.status,
                DATE(rt.created_at)  AS txn_date,
                COUNT(*)             AS txn_count,
                SUM(rt.amount)       AS total_amount,
                SUM(rt.commission)   AS total_commission,
                SUM(rt.net_amount)   AS total_net_amount,
                AVG(rt.amount)       AS avg_amount
            FROM recharge_transactions rt
            JOIN users u ON u.id = rt.user_id
            WHERE rt.deleted_at IS NULL
            GROUP BY
                rt.user_id, u.name, u.role,
                rt.operator_code, rt.recharge_type,
                rt.status, DATE(rt.created_at)
        ");

        // ── View 2: v_wallet_ledger ─────────────────────────────────────
        // Used by: wallet statement page, reconciliation
        DB::statement("
            CREATE OR REPLACE VIEW v_wallet_ledger AS
            SELECT
                wt.id,
                wt.user_id,
                u.name              AS user_name,
                wt.txn_id,
                wt.type,
                wt.amount,
                wt.balance_before,
                wt.balance_after,
                wt.description,
                wt.reference_type,
                wt.reference_id,
                wt.status,
                wt.created_at
            FROM wallet_transactions wt
            JOIN users u ON u.id = wt.user_id
        ");

        // ── View 3: v_operator_performance ─────────────────────────────
        // Used by: operator health dashboard, route management
        DB::statement("
            CREATE OR REPLACE VIEW v_operator_performance AS
            SELECT
                o.id                 AS operator_id,
                o.name               AS operator_name,
                o.code               AS operator_code,
                o.category,
                orr.id               AS route_id,
                orr.name             AS route_name,
                orr.api_provider,
                orr.priority,
                orr.success_rate,
                orr.is_active,
                COUNT(ra.id)         AS total_attempts,
                SUM(ra.status = 'success')  AS successful_attempts,
                SUM(ra.status = 'failed')   AS failed_attempts,
                SUM(ra.status = 'timeout')  AS timeout_attempts,
                AVG(ra.duration_ms)         AS avg_duration_ms,
                MAX(ra.duration_ms)         AS max_duration_ms,
                DATE(ra.created_at)         AS attempt_date
            FROM operators o
            JOIN operator_routes orr ON orr.operator_id = o.id
            LEFT JOIN recharge_attempts ra ON ra.operator_route_id = orr.id
            WHERE o.deleted_at IS NULL
              AND orr.deleted_at IS NULL
            GROUP BY
                o.id, o.name, o.code, o.category,
                orr.id, orr.name, orr.api_provider, orr.priority,
                orr.success_rate, orr.is_active, DATE(ra.created_at)
        ");

        // ── View 4: v_complaint_queue ───────────────────────────────────
        // Used by: support agent dashboard
        DB::statement("
            CREATE OR REPLACE VIEW v_complaint_queue AS
            SELECT
                c.id,
                c.ticket_number,
                c.type,
                c.status,
                c.priority,
                c.sla_deadline,
                c.sla_breached,
                c.created_at,
                u.name               AS user_name,
                u.mobile             AS user_mobile,
                rt.mobile            AS recharge_mobile,
                rt.amount            AS recharge_amount,
                rt.operator_code,
                e.name               AS assigned_employee,
                e.department         AS employee_department
            FROM complaints c
            JOIN users u ON u.id = c.user_id
            LEFT JOIN recharge_transactions rt ON rt.id = c.recharge_transaction_id
            LEFT JOIN employees e ON e.id = c.assigned_employee_id
            WHERE c.deleted_at IS NULL
              AND c.status NOT IN ('closed', 'resolved')
        ");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_complaint_queue');
        DB::statement('DROP VIEW IF EXISTS v_operator_performance');
        DB::statement('DROP VIEW IF EXISTS v_wallet_ledger');
        DB::statement('DROP VIEW IF EXISTS v_recharge_summary');
    }
};
