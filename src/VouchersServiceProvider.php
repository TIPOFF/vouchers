<?php

declare(strict_types=1);

namespace Tipoff\Vouchers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tipoff\Checkout\Contracts\VouchersService;
use Tipoff\Vouchers\Commands\VouchersCommand;
use Tipoff\Vouchers\Services\VouchersServiceImplementation;

class VouchersServiceProvider extends PackageServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        parent::boot();
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('vouchers')
            ->hasConfigFile()
            ->hasCommand(VouchersCommand::class);
    }

    public function registeringPackage()
    {
        $this->app->singleton(VouchersService::class, function () {
            return new VouchersServiceImplementation();
        });
    }
}
