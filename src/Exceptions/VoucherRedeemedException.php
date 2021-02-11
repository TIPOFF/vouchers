<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Exceptions;

use Throwable;

class VoucherRedeemedException extends \UnexpectedValueException implements VoucherException
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct('Voucher already used.', $code, $previous);
    }
}
