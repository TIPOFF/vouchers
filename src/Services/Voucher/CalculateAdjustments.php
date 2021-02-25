<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Services\Voucher;


use Tipoff\Support\Contracts\Checkout\CartInterface;
use Tipoff\Vouchers\Models\Voucher;

class CalculateAdjustments
{
    public function __invoke(CartInterface $cart)
    {
        $vouchers = Voucher::query()->byCartId($cart->getId())->get();

        $vouchers->each(function (Voucher $voucher) use ($cart) {
            if ($voucher->amount > 0) {
                $cart->addCredits($voucher->amount);
            }
        });
    }
}
