<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Listeners;

use Carbon\Carbon;
use Tipoff\Checkout\Models\OrderItem;
use Tipoff\Support\Events\Checkout\OrderItemCreated;
use Tipoff\Vouchers\Enums\VoucherSource;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Models\VoucherType;
use Tipoff\Vouchers\Notifications\VoucherCreated;

class OrderItemCreatedListener
{
    public function handle(OrderItemCreated $event): void
    {
        if ($event->isA(VoucherType::class)) {
            /** @var OrderItem $orderItem */
            $orderItem = $event->orderItem;

            /** @var VoucherType $voucherType */
            $voucherType = $event->sellable;
            $this->handleVoucherTypePurchase($orderItem, $voucherType);
        }
    }

    private function handleVoucherTypePurchase(OrderItem $orderItem, VoucherType $voucherType): self
    {
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $order = $orderItem->order;
        $user = $orderItem->order->user;

        $voucher = new Voucher;
        $voucher->source = VoucherSource::PURCHASE();
        $voucher->amount = $voucherType->amount;
        $voucher->participants = $voucherType->participants;
        $voucher->voucher_type_id = $voucherType->id;
        $voucher->location_id = $order->getLocationId();
        $voucher->expires_at = Carbon::now()->addDays($voucherType->expiration_days);
        $voucher->purchase_order_id = $order->getId();
        $voucher->user_id = $voucher->creator_id = $voucher->updater_id = $user->id;
        $voucher->save();

        if ($address = $order->getBillingAddress()) {
            $voucherAddress = $address->replicate();
            $voucherAddress->addressable()->associate($voucher)->save();
        }

        $user->notify(new VoucherCreated($voucher));

        return $this;
    }
}
