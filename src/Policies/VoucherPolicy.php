<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Tipoff\Locations\Traits\HasLocationPermissions;
use Tipoff\Support\Contracts\Models\UserInterface;
use Tipoff\Vouchers\Models\Voucher;

class VoucherPolicy
{
    use HandlesAuthorization;
    use HasLocationPermissions;

    public function viewAny(UserInterface $user): bool
    {
        return true;
    }

    public function view(UserInterface $user, Voucher $voucher): bool
    {
        return $voucher->isOwner($user) || $this->hasLocationPermission($user, 'view vouchers', $voucher->location_id);
    }

    public function create(UserInterface $user): bool
    {
        return $user->hasPermissionTo('create vouchers') ? true : false;
    }

    public function update(UserInterface $user, Voucher $voucher): bool
    {
        return $this->hasLocationPermission($user, 'update vouchers', $voucher->location_id);
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
