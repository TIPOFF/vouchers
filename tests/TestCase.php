<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests;

use Laravel\Nova\NovaCoreServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tipoff\Support\SupportServiceProvider;
use Tipoff\TestSupport\BaseTestCase;
use Tipoff\Vouchers\Tests\Support\Models;
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
            VouchersServiceProvider::class,
        ];
    }
}
