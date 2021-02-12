<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Listeners;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tipoff\Checkout\Events\BookingOrderProcessed;
use Tipoff\Checkout\Models\Order;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Notifications\PartialRedemptionVoucherCreated;
use Tipoff\Vouchers\Tests\TestCase;

class PartialRedemptionCheckTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function handles_event_with_partial_redemption()
    {
        Notification::fake();

        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->amount(1000)->create();

        /** @var Order $order */
        $order = Order::factory()->create([
            'partial_redemption_voucher_id' => $voucher,
        ]);

        event(new BookingOrderProcessed($order));

        Notification::assertSentTo(
            [$voucher->getUser()], PartialRedemptionVoucherCreated::class
        );
    }

    /** @test */
    public function handles_event_without_partial_redemption()
    {
        Notification::fake();

        /** @var Order $order */
        $order = Order::factory()->create([
            'partial_redemption_voucher_id' => null,
        ]);

        event(new BookingOrderProcessed($order));

        Notification::assertNothingSent();
    }
}
