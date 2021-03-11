<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Listeners;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tipoff\Addresses\Models\Address;
use Tipoff\Checkout\Models\Cart;
use Tipoff\Checkout\Models\Order;
use Tipoff\Support\Events\Checkout\OrderCreated;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Notifications\PartialRedemptionVoucherCreated;

class OrderCreatedListener
{
    public function handle(OrderCreated $event): void
    {
        /** @var Order $order */
        $order = $event->order;
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $vouchers = $this->getVouchersApplied($order->cart);
        $this->redeemVouchers($order, $vouchers)
            ->handlePartialRedemption($order, $vouchers);
    }

    private function getVouchersApplied(Cart $cart): Collection
    {
        return Voucher::query()->byCartId($cart->getId())->get();
    }

    private function redeemVouchers(Order $order, Collection $vouchers): self
    {
        $vouchers->each(function (Voucher $voucher) use ($order) {
            $voucher->redeem($order)->save();
        });

        return $this;
    }

    private function handlePartialRedemption(Order $order, Collection $vouchers): self
    {
        $unusedVoucherAmount = ($vouchers->sum->amount - $order->getCredits());
        if ($unusedVoucherAmount > 0) {
            /** @psalm-suppress UndefinedMagicPropertyFetch */
            $user = $order->user;

            $voucher = new Voucher;
            $voucher->amount = $unusedVoucherAmount;
            $voucher->voucher_type_id = Voucher::PARTIAL_REDEMPTION_VOUCHER_TYPE_ID;
            $voucher->location_id = $order->getLocationId();
            $voucher->expires_at = $vouchers->min->expires_at;
            $voucher->redeemable_at = Carbon::now();
            $voucher->purchase_order_id = $order->getId();
            $voucher->user_id = $voucher->creator_id = $voucher->updater_id = $user->id;
            $voucher->save();

            /** @var Voucher $sourceVoucher */
            $sourceVoucher = $vouchers->first();
            static::copyVoucherAddressesToTarget($sourceVoucher, $voucher);

            $user->notify(new PartialRedemptionVoucherCreated($voucher));
        }

        return $this;
    }

    private static function copyVoucherAddressesToTarget(Voucher $source, Voucher $target)
    {
        // TODO - replace with method in `HasAddresses` trait when available
        // $sourceVoucher->copyAddressesToTarget($voucher);
        $source->addresses
            ->each(function (Address $sourceAddress) use ($target) {
                // Create a copy, replacing the addressable with the target before saving
                $targetAddress = $sourceAddress->replicate();
                $targetAddress->addressable()->associate($target)->save();
            });
    }
}
