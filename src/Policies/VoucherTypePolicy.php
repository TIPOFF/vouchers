<?php

namespace Tipoff\Vouchers\Policies;

use App\Models\User;
use Tipoff\Vouchers\Models\VoucherType;
use Illuminate\Auth\Access\HandlesAuthorization;

class VoucherTypePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view voucher types') ? true : false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\VoucherType  $voucherType
     * @return mixed
     */
    public function view(User $user, VoucherType $voucherType)
    {
        return $user->hasPermissionTo('view voucher types') ? true : false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create voucher types') ? true : false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\VoucherType  $voucherType
     * @return mixed
     */
    public function update(User $user, VoucherType $voucherType)
    {
        return $user->hasPermissionTo('update voucher types') ? true : false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\VoucherType  $voucherType
     * @return mixed
     */
    public function delete(User $user, VoucherType $voucherType)
    {
        return $user->hasPermissionTo('delete voucher types') ? true : false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\VoucherType  $voucherType
     * @return mixed
     */
    public function restore(User $user, VoucherType $voucherType)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\VoucherType  $voucherType
     * @return mixed
     */
    public function forceDelete(User $user, VoucherType $voucherType)
    {
        return false;
    }
}
