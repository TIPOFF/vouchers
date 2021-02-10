<?php

namespace Tipoff\Vouchers\Policies;

use App\Models\User;
use Tipoff\Vouchers\Models\Voucher;
use Illuminate\Auth\Access\HandlesAuthorization;

class VoucherPolicy
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
        return $user->hasPermissionTo('view vouchers') ? true : false;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Voucher  $voucher
     * @return mixed
     */
    public function view(User $user, Voucher $voucher)
    {
        return $user->hasPermissionTo('view vouchers') ? true : false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create vouchers') ? true : false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Voucher  $voucher
     * @return mixed
     */
    public function update(User $user, Voucher $voucher)
    {
        return $user->hasPermissionTo('update vouchers') ? true : false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Voucher  $voucher
     * @return mixed
     */
    public function delete(User $user, Voucher $voucher)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Voucher  $voucher
     * @return mixed
     */
    public function restore(User $user, Voucher $voucher)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Voucher  $voucher
     * @return mixed
     */
    public function forceDelete(User $user, Voucher $voucher)
    {
        return false;
    }
}
