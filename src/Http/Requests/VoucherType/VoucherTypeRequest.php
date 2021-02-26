<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Http\Requests\VoucherType;

use Tipoff\Support\Http\Requests\BaseApiRequest;
use Tipoff\Vouchers\Models\VoucherType;

abstract class VoucherTypeRequest extends BaseApiRequest
{
    public function getModelClass(): string
    {
        return VoucherType::class;
    }

    public function authorize()
    {
        return false;
    }

    public function rules()
    {
        return [];
    }
}
