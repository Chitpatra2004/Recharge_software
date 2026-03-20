<?php

namespace App\Listeners;

use App\Services\DashboardService;

/**
 * BustDashboardCache — event-driven cache invalidation.
 *
 * Registered for three recharge events in AppServiceProvider:
 *
 *   RechargeInitiated → bust summary + live (new pending txn appeared)
 *   RechargeCompleted → bust summary + live + operators + gateway (success!)
 *   RechargeFailed    → bust summary + live + operators (failure count changed)
 *
 * This ensures KPI cards and operator health reflect reality within
 * seconds of any transaction state change, without waiting for TTL expiry.
 *
 * Does NOT bust the hourly/weekly chart — 5-minute TTL is acceptable there
 * and avoids thrashing the chart queries on every individual transaction.
 */
class BustDashboardCache
{
    /**
     * Called for all three recharge events.
     * The event object carries a $transaction property but we don't need it —
     * we just need to know "something changed" to flush the right keys.
     */
    public function handle(object $event): void
    {
        // Always bust summary + live feed
        DashboardService::bustTransactionCaches();

        // For terminal events (completed/failed), also bust operator + gateway panels
        $terminalEvents = [
            \App\Events\RechargeCompleted::class,
            \App\Events\RechargeFailed::class,
        ];

        if (in_array(get_class($event), $terminalEvents)) {
            DashboardService::bustOperatorCaches();
        }
    }
}
