<?php

declare(strict_types=1);

use Tipoff\Authorization\Permissions\BasePermissionsMigration;

class AddVoucherPermissions extends BasePermissionsMigration
{
    public function up()
    {
        $permissions = [
            'view vouchers',
            'create vouchers',
            'update vouchers',
            'view voucher types',
            'create voucher types',
            'update voucher types',
            'delete voucher types',
        ];

        $this->createPermissions($permissions);
    }
}
