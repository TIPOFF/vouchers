<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Listeners;

use Tipoff\Checkout\Events\BookingOrderProcessed;
use Tipoff\Vouchers\Notifications\PartialRedemptionVoucherCreated;

class PartialRedemptionCheck
{
    public function handle(BookingOrderProcessed $event): void
    {
        $order = $event->order;

        if ($order->hasPartialRedemptionVoucher()) {
            $order
                ->partialRedemptionVoucher
                ->customer
                ->user
                ->notify(new PartialRedemptionVoucherCreated($order->partialRedemptionVoucher));
        }
    }
}
