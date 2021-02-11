<?php

declare(strict_types=1);

namespace Tipoff\Vouchers;

use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Tipoff\Checkout\Contracts\Models\VoucherInterface;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Models\VoucherType;
use Tipoff\Vouchers\Policies\VoucherPolicy;
use Tipoff\Vouchers\Policies\VoucherTypePolicy;

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
        $this->app->bind(VoucherInterface::class, Voucher::class);

        Gate::policy(Voucher::class, VoucherPolicy::class);
        Gate::policy(VoucherType::class, VoucherTypePolicy::class);
    }
}
