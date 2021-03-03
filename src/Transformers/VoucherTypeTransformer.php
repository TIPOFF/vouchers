<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Transformers;

use League\Fractal\TransformerAbstract;
use Tipoff\Vouchers\Models\VoucherType;

class VoucherTypeTransformer extends TransformerAbstract
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
