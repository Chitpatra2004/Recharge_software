<?php

namespace App\Console\Commands;

use App\Services\DashboardService;
use Illuminate\Console\Command;

/**
 * Warms up all dashboard cache keys before the first admin request hits.
 * Schedule this every minute via the kernel for zero-latency dashboard loads.
 *
 * Usage:
 *   php artisan dashboard:warm
 *
 * In App\Console\Kernel (schedule):
 *   $schedule->command('dashboard:warm')->everyMinute()->withoutOverlapping();
 */
class WarmDashboardCache extends Command
{
    protected $signature   = 'dashboard:warm';
    protected $description = 'Pre-populate all dashboard cache keys';

    public function handle(DashboardService $dashboard): int
    {
        $sections = [
            'summary'    => fn () => $dashboard->summary(),
            'live'       => fn () => $dashboard->liveTransactionFeed(),
            'operators'  => fn () => $dashboard->operatorPerformance(),
            'gateway'    => fn () => $dashboard->gatewayPerformance(),
            'complaints' => fn () => $dashboard->pendingComplaints(),
            'chart:hourly' => fn () => $dashboard->hourlyChart(),
            'chart:weekly' => fn () => $dashboard->weeklyChart(),
        ];

        foreach ($sections as $name => $fn) {
            $start = microtime(true);
            try {
                $fn();
                $ms = round((microtime(true) - $start) * 1000);
                $this->line("  <fg=green>✓</> {$name} <fg=gray>({$ms}ms)</>");
            } catch (\Throwable $e) {
                $this->line("  <fg=red>✗</> {$name}: {$e->getMessage()}");
            }
        }

        $this->info('Dashboard cache warmed.');
        return self::SUCCESS;
    }
}
