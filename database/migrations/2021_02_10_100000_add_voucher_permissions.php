<?php

declare(strict_types=1);

use Tipoff\Authorization\Permissions\BasePermissionsMigration;

class AddVoucherPermissions extends BasePermissionsMigration
{
    public function up()
    {
        $permissions = [
            'view vouchers' => ['Owner', 'Executive', 'Staff'],
            'create vouchers' => ['Owner', 'Executive'],
            'update vouchers' => ['Owner', 'Executive'],
            'view voucher types' => ['Owner', 'Executive', 'Staff'],
            'create voucher types' => ['Owner', 'Executive'],
            'update voucher types' => ['Owner', 'Executive'],
            'delete voucher types' => []
        ];

        $this->createPermissions($permissions);
    }
}
