<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\View\Components;

use Tipoff\Checkout\Models\Cart;
use Tipoff\Checkout\Models\CartItem;
use Tipoff\Vouchers\Models\VoucherType;
use Tipoff\Vouchers\Tests\TestCase;

class VoucherTypeComponentTest extends TestCase
{
    /** @test */
    public function single_item()
    {
        /** @var VoucherType $sellable */
        $sellable = VoucherType::factory()->amount(1234)->create();
        $cart = Cart::factory()->create();
        CartItem::factory()->withSellable($sellable)->create([
            'cart_id' => $cart,
        ]);
        $cart->refresh()->save();

        $view = $this->blade(
            '<x-tipoff-cart :cart="$cart" />',
            ['cart' => $cart]
        );

        // TODO - enable after fix to dynamic component in cart component
        // $view->assertSee("Voucher Type: {$sellable->name}");
        $view->assertSee('Quantity: 1');
    }
}
