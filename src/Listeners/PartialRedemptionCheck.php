<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Listeners;

use Tipoff\Checkout\Events\BookingOrderProcessed;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Notifications\PartialRedemptionVoucherCreated;

class PartialRedemptionCheck
{
    public function handle(BookingOrderProcessed $event): void
    {
        $order = $event->order;

        /** @var Voucher $voucher */
        if ($voucher = $order->getPartialRedemptionVoucher()) {
            if ($user = $voucher->getUser()) {
                $user->notify(new PartialRedemptionVoucherCreated($voucher));
            }
        }
    }
}
