<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Tipoff\Support\Contracts\Models\UserInterface;
use Tipoff\Vouchers\Models\VoucherType;

class VoucherTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(UserInterface $user): bool
    {
        return $user->hasPermissionTo('view voucher types') ? true : false;
    }

    public function view(UserInterface $user, VoucherType $voucherType): bool
    {
        return $user->hasPermissionTo('view voucher types') ? true : false;
    }

    public function create(UserInterface $user): bool
    {
        return $user->hasPermissionTo('create voucher types') ? true : false;
    }

    public function update(UserInterface $user, VoucherType $voucherType): bool
    {
        return $user->hasPermissionTo('update voucher types') ? true : false;
    }

    public function delete(UserInterface $user, VoucherType $voucherType): bool
    {
        return $user->hasPermissionTo('delete voucher types') ? true : false;
    }

    public function restore(UserInterface $user, VoucherType $voucherType): bool
    {
        return false;
    }

    public function forceDelete(UserInterface $user, VoucherType $voucherType): bool
    {
        return false;
    }
}
