<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Listeners;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tipoff\Checkout\Models\Cart;
use Tipoff\Checkout\Models\Order;
use Tipoff\Support\Events\Checkout\OrderCreated;
use Tipoff\Vouchers\Listeners\OrderCreatedListener;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Notifications\PartialRedemptionVoucherCreated;
use Tipoff\Vouchers\Tests\TestCase;

class OrderCreatedListenerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function cart_vouchers_are_redeemed()
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'location_id' => 123,
        ]);

        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->expired(false)->create();

        /** @var Cart $cart */
        $cart = Cart::factory()->create();
        $cart->order()->associate($order)->save();
        $voucher->carts()->sync([$cart->id]);

        $listener = new OrderCreatedListener();
        $listener->handle(new OrderCreated($order));

        $vouchers = Voucher::query()->byOrderId($order->id)->get();

        $this->assertEquals(1, $vouchers->count());

        /** @var Voucher $voucher */
        $voucher = $vouchers->first();
        $this->assertNotNull($voucher->redeemed_at);
        $this->assertEquals($order->id, $voucher->order_id);
    }

    /** @test */
    public function full_redemption_is_handled()
    {
        Notification::fake();

        /** @var Order $order */
        $order = Order::factory()->create([
            'location_id' => 123,
            'credits' => 1000,
        ]);

        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->expired(false)->amount(1000)->create();

        /** @var Cart $cart */
        $cart = Cart::factory()->create();
        $cart->order()->associate($order)->save();
        $voucher->carts()->sync([$cart->id]);

        $listener = new OrderCreatedListener();
        $listener->handle(new OrderCreated($order));

        Notification::assertNothingSent();
    }

    /** @test */
    public function partial_redemption_is_handled()
    {
        Notification::fake();

        /** @var Order $order */
        $order = Order::factory()->create([
            'location_id' => 123,
            'credits' => 500,
        ]);

        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->expired(false)->amount(1000)->create();

        /** @var Cart $cart */
        $cart = Cart::factory()->create();
        $cart->order()->associate($order)->save();
        $voucher->carts()->sync([$cart->id]);

        $listener = new OrderCreatedListener();
        $listener->handle(new OrderCreated($order));

        Notification::assertSentTo(
            [$voucher->getUser()],
            PartialRedemptionVoucherCreated::class
        );

        $vouchers = Voucher::query()
            ->where('user_id', '=', $voucher->getUser()->id)
            ->where('voucher_type_id', '=', Voucher::PARTIAL_REDEMPTION_VOUCHER_TYPE_ID)
            ->get();
        $this->assertCount(1, $vouchers);

        /** @var Voucher $newVoucher */
        $newVoucher = $vouchers->first();
        $this->assertEquals(500, $newVoucher->amount);
        $this->assertEquals($voucher->expires_at, $newVoucher->expires_at);
    }
}
