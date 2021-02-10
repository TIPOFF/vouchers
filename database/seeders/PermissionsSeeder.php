<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        /** @var Permission $api */
        if ($api = app(Permission::class)) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            foreach ([
                'view vouchers',
                'create vouchers',
                'update vouchers',
                'view voucher types',
                'create voucher types',
                'update voucher types',
                'delete voucher types',
            ] as $name) {
                $api::findOrCreate($name, null);
            };
        }
    }
}
