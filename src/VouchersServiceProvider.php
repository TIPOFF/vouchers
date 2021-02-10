<?php

declare(strict_types=1);

namespace Tipoff\Vouchers;

use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Models\VoucherType;
use Tipoff\Vouchers\Policies\VoucherPolicy;
use Tipoff\Vouchers\Policies\VoucherTypePolicy;
use Tipoff\Vouchers\Services\VouchersService;

class VouchersServiceProvider extends PackageServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        parent::boot();
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('vouchers')
            ->hasConfigFile();
    }

    public function registeringPackage()
    {
        $this->app->singleton(VouchersService::class, function () {
            return new VouchersService();
        });

        Gate::policy(Voucher::class, VoucherPolicy::class);
        Gate::policy(VoucherType::class, VoucherTypePolicy::class);
    }
}
