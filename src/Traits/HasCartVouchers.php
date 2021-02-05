<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Traits;

use Assert\Assert;
use Brick\Money\Money;
use Tipoff\Checkout\Contracts\CartInterface;
use Tipoff\Checkout\Contracts\CheckoutService;
use Tipoff\Checkout\Contracts\VouchersService;
use Tipoff\Checkout\Models\Cart;

trait HasCartVouchers
{
    /**
     * @param string $voucherCode
     * @return self
     */
    public function applyVoucherCode(string $voucherCode)
    {
        $cart = $this->getCartInterface();
        if (app(VouchersService::class)->applyCodeToCart($cart, $voucherCode)) {
            $cart->updateTotalCartDeductions();

            return $this;
        }

        throw new \Exception("Code {$voucherCode} is invalid.");
    }

    public function calculateVouchersTotal(): Money
    {
        return app(VouchersService::class)->calculateDeductions($this->getCartInterface());
    }

    public function markVouchersAsUsed(): self
    {
        app(VouchersService::class)->markVouchersAsUsed($this, $this->order_id);


        $orderId = $this->order_id;

        $this->vouchers()->each(function ($voucher) use ($orderId) {
            $voucher->redeem();
            $voucher->order_id = $orderId;
            $voucher->save();
        });

        return $this;
    }

    public function issuePartialRedemptionVoucher(): Cart
    {
        $cart = $this->getCartInterface();

        if ($cart->total_deductions < $cart->amount + $cart->total_taxes + $cart->total_fees) {
            return $cart;
        }

        app(CheckoutService::class)->issueCartPartialRedemptionVoucher($cart);

        return $cart;
    }

    protected function getCartInterface(): CartInterface
    {
        Assert::that($this)->isInstanceOf(CartInterface::class);

        return $this;
    }

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
