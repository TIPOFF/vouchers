<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Laravel\Nova\NovaCoreServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use ReflectionClass;
use Tipoff\Vouchers\Tests\Support\Models;
use Tipoff\Vouchers\Tests\Support\Nova;
use Tipoff\Vouchers\Tests\Support\Providers\NovaTestbenchServiceProvider;
use Tipoff\Support\SupportServiceProvider;
use Tipoff\Vouchers\VouchersServiceProvider;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Tipoff\\Vouchers\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $this->artisan('migrate', ['--database' => 'testing'])->run();
    }

    protected function getPackageProviders($app)
    {
        return [
            NovaCoreServiceProvider::class,
            NovaTestbenchServiceProvider::class,
            SupportServiceProvider::class,
            VouchersServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        // TODO - refactor into common TestCase base provided by support
        // Fix for Nova guessing namespace for local resources
        $property = (new ReflectionClass($app))->getProperty('namespace');
        $property->setAccessible(true);
        $property->setValue($app, 'Tipoff\\Vouchers\\');

        $app['config']->set('vouchers.model_class', [
            'user' => Models\User::class,
            'cart' => Models\Cart::class,
            'order' => Models\Order::class,
            'location' => Models\Location::class,
            'customer' => Models\Customer::class,
        ]);
        $app['config']->set('vouchers.nova_class', [
            'user' => Nova\User::class,
            'order' => Nova\Order::class,
            'location' => Nova\Location::class,
            'customer' => Nova\Customer::class,
        ]);

        // Create stub tables to satisfy FK dependencies
        foreach(config('vouchers.model_class') as $class) {
            $class::createTable();
        }
    }
}
