<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Tipoff\Support\Contracts\Models\UserInterface;
use Tipoff\Vouchers\Models\Voucher;

class VoucherPolicy
{
    use HandlesAuthorization;

    public function viewAny(UserInterface $user): bool
    {
        return $user->hasPermissionTo('view vouchers') ? true : false;
    }

    public function view(UserInterface $user, Voucher $voucher): bool
    {
        return $user->hasPermissionTo('view vouchers') ? true : false;
    }

    public function create(UserInterface $user): bool
    {
        return $user->hasPermissionTo('create vouchers') ? true : false;
    }

    public function update(UserInterface $user, Voucher $voucher): bool
    {
        return $user->hasPermissionTo('update vouchers') ? true : false;
    }

    public function delete(UserInterface $user, Voucher $voucher): bool
    {
        return false;
    }

    public function restore(UserInterface $user, Voucher $voucher): bool
    {
        return false;
    }

    public function forceDelete(UserInterface $user, Voucher $voucher): bool
    {
        return false;
    }
}
