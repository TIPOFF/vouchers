<?php

namespace Tipoff\Vouchers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tipoff\Vouchers\Commands\VouchersCommand;

class VouchersServiceProvider extends PackageServiceProvider
{
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
            ->hasViews()
            ->hasMigration('create_vouchers_table')
            ->hasCommand(VouchersCommand::class);
    }
}
