<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Artisan command to automatically add next-month partitions
 * and optionally archive/drop old partitions.
 *
 * Schedule: monthly, first day of each month.
 *   $schedule->command('db:maintain-partitions')->monthlyOn(1, '00:30');
 */
class MaintainPartitions extends Command
{
    protected $signature = 'db:maintain-partitions
                            {--dry-run : Print SQL without executing}
                            {--archive-months=3 : Drop partitions older than N months}';

    protected $description = 'Add next-month partitions and archive old ones for high-volume tables';

    /** Tables that use RANGE partitioning */
    private array $partitionedTables = [
        'recharge_transactions' => 'rt',
        'wallet_transactions'   => 'wt',
        'activity_logs'         => 'al',
    ];

    public function handle(): int
    {
        $dryRun        = $this->option('dry-run');
        $archiveMonths = (int) $this->option('archive-months');
        $nextMonth     = Carbon::now()->addMonth()->startOfMonth();
        $cutoff        = Carbon::now()->subMonths($archiveMonths)->startOfMonth();

        foreach ($this->partitionedTables as $table => $prefix) {
            $this->info("── {$table} ──────────────────────────────────────");

            // Add next month partition
            $partitionName = "{$prefix}_" . $nextMonth->format('Y_m');
            $lessThan      = $nextMonth->addMonth()->unix();
            $addSql = "ALTER TABLE `{$table}` REORGANIZE PARTITION {$prefix}_future INTO (
                PARTITION {$partitionName} VALUES LESS THAN ({$lessThan}),
                PARTITION {$prefix}_future  VALUES LESS THAN MAXVALUE
            )";

            if ($dryRun) {
                $this->line("<comment>ADD:</comment> {$addSql}");
            } else {
                try {
                    DB::statement($addSql);
                    $this->line("<info>✓ Added partition {$partitionName}</info>");
                } catch (\Throwable $e) {
                    $this->warn("  Skip (may already exist): " . $e->getMessage());
                }
            }

            // Archive (drop) old partition if beyond cutoff
            $oldPartition = "{$prefix}_" . $cutoff->format('Y_m');
            $dropSql      = "ALTER TABLE `{$table}` DROP PARTITION IF EXISTS {$oldPartition}";

            if ($dryRun) {
                $this->line("<comment>DROP (archive):</comment> {$dropSql}");
            } else {
                try {
                    DB::statement($dropSql);
                    $this->line("<info>✓ Archived partition {$oldPartition}</info>");
                } catch (\Throwable $e) {
                    $this->warn("  Skip archive: " . $e->getMessage());
                }
            }
        }

        $this->info('Partition maintenance complete.');
        return self::SUCCESS;
    }
}
