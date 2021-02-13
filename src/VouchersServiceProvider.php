<?php

declare(strict_types=1);

namespace Tipoff\Vouchers;

use Tipoff\Support\TipoffPackage;
use Tipoff\Support\TipoffServiceProvider;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Policies\VoucherPolicy;
use Tipoff\Vouchers\Policies\VoucherTypePolicy;

class VouchersServiceProvider extends TipoffServiceProvider
{
    public function configureTipoffPackage(TipoffPackage $package): void
    {
        // Base configuration
        $package
            ->hasPolicies([
                Voucher::class => VoucherPolicy::class,
                Voucher::class => VoucherTypePolicy::class,
            ])
            ->name('vouchers')
            ->hasConfigFile();
    }
}
