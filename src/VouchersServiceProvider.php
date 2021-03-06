<?php

declare(strict_types=1);

namespace Tipoff\Vouchers;

use Tipoff\Support\Contracts\Checkout\Vouchers\VoucherInterface;
use Tipoff\Support\TipoffPackage;
use Tipoff\Support\TipoffServiceProvider;
use Tipoff\Vouchers\Commands\VouchersValidate;
use Tipoff\Vouchers\Listeners\OrderCreatedListener;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Models\VoucherType;
use Tipoff\Vouchers\Policies\VoucherPolicy;
use Tipoff\Vouchers\Policies\VoucherTypePolicy;
use Tipoff\Vouchers\View\Components;

class VouchersServiceProvider extends TipoffServiceProvider
{
    public function configureTipoffPackage(TipoffPackage $package): void
    {
        // Base configuration
        $package
            ->name('vouchers')
            ->hasViews()
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
                OrderCreatedListener::class => [
                    OrderCreatedListener::class,
                ],
            ])
            ->hasBladeComponents([
                'voucher-cart-deduction' => Components\Cart\VoucherComponent::class,
                'voucher-type-cart-item' => Components\Cart\VoucherTypeComponent::class,
                'voucher-order-deduction' => Components\Order\VoucherComponent::class,
                'voucher-type-order-item' => Components\Order\VoucherTypeComponent::class,
            ])
            ->hasApiRoute('api')
            ->hasCommands([
                VouchersValidate::class,
            ]);
    }
}
