<?php

namespace App\Listeners;

use App\Services\ActivityLogger;

class LogRechargeActivity
{
    /**
     * Handles RechargeInitiated, RechargeCompleted, RechargeFailed events.
     * Single listener, dispatches based on event class name.
     */
    public function handle(object $event): void
    {
        $t      = $event->transaction;
        $action = match (get_class($event)) {
            \App\Events\RechargeInitiated::class => 'recharge.initiated',
            \App\Events\RechargeCompleted::class => 'recharge.completed',
            \App\Events\RechargeFailed::class    => 'recharge.failed',
            default                              => 'recharge.unknown',
        };

        ActivityLogger::log(
            action:      $action,
            description: "{$action} — Mobile: {$t->mobile}, Amount: {$t->amount}",
            subject:     $t,
            properties:  [
                'status'        => $t->status,
                'operator_code' => $t->operator_code,
                'amount'        => $t->amount,
                'operator_ref'  => $t->operator_ref,
            ],
            userId: $t->user_id,
        );
    }
}
