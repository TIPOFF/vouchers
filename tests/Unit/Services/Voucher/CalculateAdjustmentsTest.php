<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Services\Voucher;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Checkout\Models\Cart;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Tests\Support\Models\TestSellable;
use Tipoff\Vouchers\Tests\TestCase;

class CalculateAdjustmentsTest extends TestCase
{
    use DatabaseTransactions;

    private TestSellable $sellable;
    private Cart $cart;

    public function setUp(): void
    {
        parent::setUp();

        TestSellable::createTable();
        $this->sellable = TestSellable::factory()->create();
        $this->cart = Cart::factory()->create();
    }

    /** @test */
    public function calculate_credit_with_no_voucher()
    {
        $this->withCart([
            [2500, 1],
        ], function ($cart) {
            Voucher::calculateAdjustments($cart);
        });

        $cart = $this->cart;
        $this->assertEquals(0, $cart->getCredits());
    }

    /** @test */
    public function calculate_credit_with_amount_voucher()
    {
        $this->withCart([
            [2500, 1],
        ], function ($cart) {
            /** @var Voucher $voucher */
            $voucher = Voucher::factory()->amount(1000)->create();

            $voucher->applyToCart($cart);

            Voucher::calculateAdjustments($cart);
        });

        $cart = $this->cart;
        $this->assertEquals(1000, $cart->getCredits());
    }

    /** @test */
    public function calculate_credit_with_multiple_vouchers()
    {
        $this->withCart([
            [2500, 1],
        ], function ($cart) {
            /** @var Voucher $voucher1 */
            $voucher1 = Voucher::factory()->amount(1000)->create();

            /** @var Voucher $voucher2 */
            $voucher2 = Voucher::factory()->amount(500)->create();

            $voucher1->applyToCart($cart);
            $voucher2->applyToCart($cart);

            Voucher::calculateAdjustments($cart);
        });

        $cart = $this->cart;
        $this->assertEquals(1500, $cart->getCredits());
    }

    /** @test */
    public function ensure_credit_is_capped()
    {
        $this->withCart([
            [500, 1],
        ], function ($cart) {
            /** @var Voucher $voucher */
            $voucher = Voucher::factory()->amount(1000)->create();

            $voucher->applyToCart($cart);

            Voucher::calculateAdjustments($cart);
        });

        $cart = $this->cart;
        $this->assertEquals(500, $cart->getCredits());
    }

    private function addCartItems(array $items): Cart
    {
        foreach ($items as $idx => $item) {
            [$amount, $quantity] = $item;

            $this->cart->upsertItem(
                Cart::createItem($this->sellable, "item-{$idx}", $amount, $quantity)
            );
        }

        return $this->cart;
    }

    private function withCart(array $items, \Closure $closure)
    {
        $result = ($closure)($this->addCartItems($items));

        // Save results so we can inspect
        $this->cart->cartItems->each->save();
        $this->cart->save();

        return $result;
    }
}
