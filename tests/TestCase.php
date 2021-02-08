<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests;

use Laravel\Nova\NovaCoreServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tipoff\Support\SupportServiceProvider;
use Tipoff\Vouchers\Tests\Support\Models;
use Tipoff\Vouchers\Tests\Support\Providers\NovaTestbenchServiceProvider;
use Tipoff\Vouchers\VouchersServiceProvider;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'testing'])->run();

        // Create stub tables for stub models to satisfy possible FK dependencies
        foreach (config('tipoff.model_class') as $class) {
            if (method_exists($class, 'createTable')) {
                $class::createTable();
            }
        }
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

    // TODO - replace w/helper in future version of tipoff/support
    private function createNovaResourceStub(string $novaClass, string $modelClass): void
    {
        if (class_exists($novaClass)) {
            return;
        }

        $classBasename = class_basename($novaClass);
        $classNamespace = substr($novaClass, 0, strrpos($novaClass, '\\'));

        $classDef = <<<EOT
namespace {$classNamespace};

use Illuminate\Http\Request;
use Laravel\Nova\Resource;

class {$classBasename} extends Resource
{
    public static \$model = \\{$modelClass}::class;

    public function fields(Request \$request)
    {
    }
}
EOT;
        // alias the anonymous class with your class name
        eval($classDef);
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('tipoff.model_class.user', Models\User::class);

        // Create stub tables to satisfy FK dependencies
        foreach (config('tipoff.model_class') as $modelClass) {
            createModelStub($modelClass);
        }

        // Create nova resource stubs for anything not already defined
        foreach (config('vouchers.nova_class') as $alias => $novaClass) {
            $this->createNovaResourceStub($novaClass, config('tipoff.model_class.'.$alias));
        }
    }
}
