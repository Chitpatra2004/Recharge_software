<?php

namespace App\Providers;

use App\Contracts\Repositories\OperatorRepositoryInterface;
use App\Contracts\Repositories\RechargeRepositoryInterface;
use App\Contracts\Repositories\WalletRepositoryInterface;
use App\Contracts\Services\RechargeServiceInterface;
use App\Contracts\Services\WalletServiceInterface;
use App\Repositories\OperatorRepository;
use App\Repositories\RechargeRepository;
use App\Repositories\WalletRepository;
use App\Services\RechargeService;
use App\Services\WalletService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(RechargeRepositoryInterface::class, RechargeRepository::class);
        $this->app->bind(WalletRepositoryInterface::class,   WalletRepository::class);
        $this->app->bind(OperatorRepositoryInterface::class, OperatorRepository::class);

        // Services
        $this->app->bind(RechargeServiceInterface::class, RechargeService::class);
        $this->app->bind(WalletServiceInterface::class,   WalletService::class);
    }
}
