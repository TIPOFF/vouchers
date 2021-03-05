<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\View\Components\Order;

use Tipoff\Checkout\Models\Cart;
use Tipoff\Checkout\Models\CartItem;
use Tipoff\Checkout\Models\Order;
use Tipoff\Checkout\Models\OrderItem;
use Tipoff\Vouchers\Models\VoucherType;
use Tipoff\Vouchers\Tests\TestCase;

class VoucherTypeComponentTest extends TestCase
{
    /** @test */
    public function single_item()
    {
        /** @var VoucherType $sellable */
        $sellable = VoucherType::factory()->amount(1234)->create();
        $order = Order::factory()->create();
        OrderItem::factory()->withSellable($sellable)->create([
            'order_id' => $order,
            'quantity' => 1,
        ]);
        $order->refresh()->save();

        $view = $this->blade(
            '<x-tipoff-order :order="$order" />',
            ['order' => $order]
        );

        $view->assertSee("Voucher Type: {$sellable->name}");
    }
}
