<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests;

use Laravel\Nova\NovaCoreServiceProvider;
use Spatie\Permission\PermissionServiceProvider;
use Tipoff\Addresses\AddressesServiceProvider;
use Tipoff\Authorization\AuthorizationServiceProvider;
use Tipoff\Checkout\CheckoutServiceProvider;
use Tipoff\Locations\LocationsServiceProvider;
use Tipoff\Support\SupportServiceProvider;
use Tipoff\TestSupport\BaseTestCase;
use Tipoff\Vouchers\Tests\Support\Providers\NovaPackageServiceProvider;
use Tipoff\Vouchers\VouchersServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            NovaCoreServiceProvider::class,
            NovaPackageServiceProvider::class,
            SupportServiceProvider::class,
            AuthorizationServiceProvider::class,
            AddressesServiceProvider::class,
            LocationsServiceProvider::class,
            PermissionServiceProvider::class,
            CheckoutServiceProvider::class,
            VouchersServiceProvider::class,
        ];
    }
}
