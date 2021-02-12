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

        if ($voucher = $order->getPartialRedemptionVoucher()) {
            /** @var Voucher $voucher */
            if ($user = $voucher->getUser()) {
                $user->notify(new PartialRedemptionVoucherCreated($voucher));
            }
        }
    }
}
