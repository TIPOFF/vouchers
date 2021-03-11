<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Listeners;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tipoff\Checkout\Models\Order;
use Tipoff\Checkout\Models\OrderItem;
use Tipoff\Locations\Models\Location;
use Tipoff\Support\Events\Checkout\OrderItemCreated;
use Tipoff\TestSupport\Models\TestSellable;
use Tipoff\Vouchers\Listeners\OrderItemCreatedListener;
use Tipoff\Vouchers\Models\VoucherType;
use Tipoff\Vouchers\Notifications\VoucherCreated;
use Tipoff\Vouchers\Tests\TestCase;

class OrderItemCreatedListenerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function non_voucher_types_ignored()
    {
        Notification::fake();

        TestSellable::createTable();
        $sellable = TestSellable::factory()->create();
        $orderItem = OrderItem::factory()->withSellable($sellable)->create();

        $listener = new OrderItemCreatedListener();
        $listener->handle(new OrderItemCreated($orderItem));

        $this->assertDatabaseCount('vouchers', 0);

        Notification::assertNothingSent();
    }

    /** @test */
    public function voucher_created_from_voucher_type()
    {
        Notification::fake();

        $voucherType = VoucherType::factory()->create();
        $orderItem = OrderItem::factory()->withSellable($voucherType)->create([
            'order_id' => Order::factory()->create([
                'location_id' => Location::factory()->create(),
            ]),
        ]);

        $listener = new OrderItemCreatedListener();
        $listener->handle(new OrderItemCreated($orderItem));

        $this->assertDatabaseCount('vouchers', 1);

        Notification::assertSentTo(
            [$orderItem->order->getUser()],
            VoucherCreated::class
        );
    }
}
