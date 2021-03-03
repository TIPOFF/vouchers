<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Transformers;

use Tipoff\Support\Transformers\BaseTransformer;
use Tipoff\Vouchers\Models\VoucherType;

class VoucherTypeTransformer extends BaseTransformer
{
    protected $defaultIncludes = [
    ];

    protected $availableIncludes = [
    ];

    public function transform(VoucherType $voucherType)
    {
        return [
            'id' => $voucherType->id,
            'name' => $voucherType->name,
            'title' => $voucherType->title,
            'sell_price' => $voucherType->sell_price,
            'amount' => $voucherType->amount,
            'participants' => $voucherType->participants,
            'expiration_days' => $voucherType->expiration_days,
        ];
    }
}
