<?php

declare(strict_types=1);

use Tipoff\Authorization\Permissions\BasePermissionsMigration;

class AddVoucherPermissions extends BasePermissionsMigration
{
    public function up()
    {
        $permissions = [
            'view vouchers' => ['Owner', 'Staff'],
            'create vouchers' => ['Owner'],
            'update vouchers' => ['Owner'],
            'view voucher types' => ['Owner', 'Staff'],
            'create voucher types' => ['Owner'],
            'update voucher types' => ['Owner'],
            'delete voucher types' => []
        ];

        $this->createPermissions($permissions);
    }
}
