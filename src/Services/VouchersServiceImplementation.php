<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Services;

use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Tipoff\Checkout\Contracts\CartInterface;
use Tipoff\Checkout\Contracts\VouchersService;
use Tipoff\Vouchers\Models\Voucher;

class VouchersServiceImplementation implements VouchersService
{
    public function generateVoucherCode(): string
    {
        do {
            $code = Carbon::now('America/New_York')->format('ymd').Str::upper(Str::random(3));
        } while (Voucher::where('code', $code)->first());

        return $code;
    }

    public function issueCartPartialRedemptionVoucher($cart)
    {
        $order = $cart->order;
        $voucher = Voucher::create([
            'location_id' => $cart->location_id,
            'customer_id' => $cart->user_id,
            'voucher_type_id' => Cart::PARTIAL_REDEMPTION_VOUCHER_TYPE_ID,
            'redeemable_at' => now(),
            'amount' => $cart->total_deductions - ($cart->amount + $cart->total_taxes + $cart->total_fees),
            'expires_at' => $cart->vouchers()->first()->expires_at,
            'creator_id' => $cart->user_id,
            'updater_id' => $cart->user_id,
        ]);

        $order->partial_redemption_voucher_id = $voucher->id;
        $order->save();

        return $voucher;
    }

    public function calculateDeductions(CartInterface $cart): Money
    {
        $total = Money::ofMinor(0, 'USD');

        foreach ($cart->vouchers()->get() as $voucher) {
            $amount = Money::ofMinor($voucher->amount, 'USD');
            if ($amount->isPositive()) {
                $total = $total->plus($amount);
            }
        }

        return $total;
    }

    public function applyCodeToCart(CartInterface $cart, string $code): bool
    {
        if ($voucher = Voucher::validAt()->where('code', $code)->first()) {
            if ($voucher->participants > 0) {
                throw new \Exception('Participants vouchers not supported yet.');
            }

            if (! empty($voucher->redeemed_at)) {
                throw new \Exception('Voucher already used.');
            }

            if ($voucher->amount > 0) {
                $cart->vouchers()->syncWithoutDetaching([$voucher->id]);
                $cart->updateTotalCartDeductions();
            }

            return true;
        }

        throw new \Exception("Code {$code} is invalid.");
    }

    public function markVouchersAsUsed(CartInterface $cart, int $orderId)
    {
        $this->vouchers()->each(function ($voucher) use ($orderId) {
            $voucher->redeem();
            $voucher->order_id = $orderId;
            $voucher->save();
        });

        return $this;
    }

}
