<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Http\Requests\Voucher;

use Tipoff\Support\Http\Requests\BaseApiRequest;
use Tipoff\Vouchers\Models\Voucher;

abstract class VoucherRequest extends BaseApiRequest
{
    public function getModelClass(): string
    {
        return Voucher::class;
    }

    public function rules()
    {
        return [];
    }
}
