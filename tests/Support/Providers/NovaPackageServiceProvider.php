<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Support\Providers;

use Tipoff\TestSupport\Providers\BaseNovaPackageServiceProvider;
use Tipoff\Vouchers\Nova\Voucher;
use Tipoff\Vouchers\Nova\VoucherType;

class NovaPackageServiceProvider extends BaseNovaPackageServiceProvider
{
    public static array $packageResources = [
        Voucher::class,
        VoucherType::class,
    ];
}
