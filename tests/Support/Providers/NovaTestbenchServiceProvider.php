<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Support\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Tipoff\Vouchers\Nova\Voucher;
use Tipoff\Vouchers\Nova\VoucherType;

class NovaTestbenchServiceProvider extends NovaApplicationServiceProvider
{
    protected function resources()
    {
        Nova::resources(array_merge(config('vouchers.nova_class'), [
            Voucher::class,
            VoucherType::class,
        ]));
    }

    protected function routes()
    {
        Nova::routes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return true;
        });
    }
}
