<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Exceptions;

class UnsupportedVoucherTypeException extends \UnexpectedValueException implements VoucherException
{
}
