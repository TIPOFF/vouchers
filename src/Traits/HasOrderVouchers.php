<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Traits;

use Tipoff\Vouchers\Models\Voucher;

trait HasOrderVouchers
{
    public function partialRedemptionVoucher()
    {
        return $this->belongsTo(Voucher::class, 'partial_redemption_voucher_id');
    }

    public function purchasedVouchers()
    {
        return $this->hasMany(Voucher::class, 'purchase_order_id');
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }
}
