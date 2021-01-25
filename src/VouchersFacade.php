<?php

namespace Tipoff\Vouchers;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tipoff\Vouchers\Vouchers
 */
class VouchersFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'vouchers';
    }
}
