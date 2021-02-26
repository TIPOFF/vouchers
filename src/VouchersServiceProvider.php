<?php

declare(strict_types=1);

namespace Tipoff\Vouchers;

use Tipoff\Checkout\Events\BookingOrderProcessed;
use Tipoff\Support\Contracts\Checkout\Vouchers\VoucherInterface;
use Tipoff\Support\TipoffPackage;
use Tipoff\Support\TipoffServiceProvider;
use Tipoff\Vouchers\Listeners\OrderCreatedListener;
use Tipoff\Vouchers\Listeners\PartialRedemptionCheck;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Models\VoucherType;
use Tipoff\Vouchers\Policies\VoucherPolicy;
use Tipoff\Vouchers\Policies\VoucherTypePolicy;

class VouchersServiceProvider extends TipoffServiceProvider
{
    public function configureTipoffPackage(TipoffPackage $package): void
    {
        // Base configuration
        $package
            ->name('vouchers')
            ->hasConfigFile();

        // Tipoff configuration
        $package
            ->hasPolicies([
                Voucher::class => VoucherPolicy::class,
                VoucherType::class => VoucherTypePolicy::class,
            ])
            ->hasNovaResources([
                \Tipoff\Vouchers\Nova\Voucher::class,
                \Tipoff\Vouchers\Nova\VoucherType::class,
            ])
            ->hasModelInterfaces([
                VoucherInterface::class => Voucher::class,
            ])
            ->hasEvents([
                BookingOrderProcessed::class => [
                    PartialRedemptionCheck::class,
                ],
                OrderCreatedListener::class => [
                    OrderCreatedListener::class,
                ],
            ]);
    }
}
