<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Models;

use Assert\LazyAssertionException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Authorization\Models\User;
use Tipoff\Checkout\Models\Cart;
use Tipoff\Vouchers\Exceptions\UnsupportedVoucherTypeException;
use Tipoff\Vouchers\Exceptions\VoucherRedeemedException;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Models\VoucherType;
use Tipoff\Vouchers\Tests\TestCase;

class VoucherModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function create()
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->create();
        $this->assertNotNull($voucher);
    }

    /** @test */
    public function creator_and_updater_are_set()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->create([
            'creator_id' => null,
            'updater_id' => null,
        ]);

        $this->assertNotNull($voucher->creator_id);
        $this->assertEquals($creator->id, $voucher->creator_id);
        $this->assertInstanceOf(Model::class, $voucher->creator);

        $this->assertNotNull($voucher->updater_id);
        $this->assertEquals($creator->id, $voucher->updater_id);
        $this->assertInstanceOf(Model::class, $voucher->updater);
    }

    /** @test */
    public function updater_is_set()
    {
        $creator = User::factory()->create();
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->create([
            'code' => 'ABCD',
            'creator_id' => $creator,
            'updater_id' => $creator,
        ]);

        $updater = User::factory()->create();
        $this->assertNotEquals($creator->id, $updater->id);

        $this->actingAs($updater);

        $voucher->code = 'HIJK';
        $voucher->save();

        $this->assertEquals($updater->id, $voucher->updater_id);
        $this->assertInstanceOf(Model::class, $voucher->updater);
    }

    /** @test */
    public function code_is_normalized()
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->create();

        $voucher->code = 'abcd';
        $voucher->save();

        $this->assertEquals('ABCD', $voucher->code);
    }

    /** @test */
    public function generate_code()
    {
        $code = Voucher::generateVoucherCode();

        $this->assertStringStartsWith(Carbon::now('America/New_York')->format('ymd'), $code);
    }

    /** @test */
    public function default_expiration_days()
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->create([
            'expires_at' => null,
        ]);

        $this->assertNotNull($voucher->expires_at);
        $this->assertEquals(VoucherType::DEFAULT_EXPIRATION_DAYS, Carbon::now()->diffInDays($voucher->expires_at));
    }

    /** @test */
    public function default_redeemable_at()
    {
        try {
            // Stabilize to return same time always
            Carbon::setTestNow(Carbon::now());

            /** @var Voucher $voucher */
            $voucher = Voucher::factory()->create([
                'redeemable_at' => null,
            ]);

            $this->assertNotNull($voucher->redeemable_at);
            $this->assertEquals(Voucher::DEFAULT_REDEEMABLE_HOURS, Carbon::now()->diffInHours($voucher->redeemable_at));
        } finally {
            Carbon::setTestNow(null);
        }
    }

    /** @test */
    public function missing_amount_and_participants()
    {
        $this->expectException(LazyAssertionException::class);
        $this->expectExceptionMessage('amount: A voucher must have either an amount or number of participants.');

        Voucher::factory()->create([
            'amount' => null,
            'participants' => null,
        ]);
    }

    /** @test */
    public function both_amount_and_participants()
    {
        $this->expectException(LazyAssertionException::class);
        $this->expectExceptionMessage('amount: A voucher cannot have both an amount & number of participants.');

        Voucher::factory()->create([
            'amount' => 4,
            'participants' => 5,
        ]);
    }

    /**
     * @test
     * @dataProvider data_provider_for_is_valid_at
     */
    public function is_valid_at(Carbon $expiresAt, Carbon $redeemableAt, ?Carbon $redeemedAt, $validAt, bool $expected)
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()
            ->create([
                'expires_at' => $expiresAt,
                'redeemable_at' => $redeemableAt,
                'redeemed_at' => $redeemedAt,
            ]);

        $this->assertEquals($expected, $voucher->isValidAt($validAt));
    }

    public function data_provider_for_is_valid_at()
    {
        return [
            'valid' => [ Carbon::now()->addDay(), Carbon::now()->subDay(), null, Carbon::now(), true ],
            'valid_now' => [ Carbon::now()->addDay(), Carbon::now()->subDay(), null, 'now', true ],
            'valid_next_week' => [ Carbon::now()->addDay(), Carbon::now()->subDay(), null, 'next week', false ],
            'expired' => [ Carbon::now()->subDay(), Carbon::now()->subDay(), null, Carbon::now(), false ],
            'expired_string' => [ Carbon::now()->subDay(), Carbon::now()->subDay(), null, Carbon::now()->format('Ymd'), false ],
            'not_redeemable' => [ Carbon::now()->addDay(), Carbon::now()->addDay(), null, Carbon::now(), false ],
            'redeemed' => [ Carbon::now()->addDay(), Carbon::now()->subDay(), Carbon::now(), Carbon::now(), false ],
            'all_reasons' => [ Carbon::now()->subDay(), Carbon::now()->addDay(), Carbon::now(), Carbon::now(), false ],
        ];
    }

    /**
     * @test
     * @dataProvider data_provider_for_decorated_amount
     */
    public function decorated_amount(int $amount, string $expected)
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()
            ->create([
                'amount' => $amount,
                'participants' => null,
            ]);

        $this->assertEquals($expected, $voucher->decoratedAmount());
    }

    public function data_provider_for_decorated_amount()
    {
        return [
            'whole' => [ 1000, '$10.00' ],
            'pennies' => [ 12, '$0.12' ],
            'thousands' => [ 12345678, '$123,456.78' ],
        ];
    }

    /** @test */
    public function redeem()
    {
        try {
            $now = Carbon::now();
            Carbon::setTestNow($now);

            /** @var Voucher $voucher */
            $voucher = Voucher::factory()->redeemed(false)->create();

            $this->assertNull($voucher->redeemed_at);

            $voucher->redeem();

            $this->assertNotNull($voucher->redeemed_at);
            $this->assertEquals($now->getTimestamp(), $voucher->redeemed_at->getTimestamp());
        } finally {
            Carbon::setTestNow(null);
        }
    }

    /** @test */
    public function scope_by_valid_at()
    {
        $today = new Carbon('today');

        // Expired
        Voucher::factory()->redeemable()->expired()->count(1)->create();

        $count = Voucher::query()->validAt($today)->count();
        $this->assertEquals(0, $count);

        // Redeemed
        Voucher::factory()->redeemable()->redeemed(true)->count(1)->create();

        $count = Voucher::query()->validAt($today)->count();
        $this->assertEquals(0, $count);

        // Not Redeemable
        Voucher::factory()->redeemable(false)->count(1)->create();

        $count = Voucher::query()->validAt($today)->count();
        $this->assertEquals(0, $count);

        // Valid
        Voucher::factory()->redeemable()->count(2)->create();

        $count = Voucher::query()->validAt($today)->count();
        $this->assertEquals(2, $count);
    }

    /** @test */
    public function cart_relation()
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->create();

        /** @var Cart $cart */
        $cart = Cart::factory()->create();
        $voucher->carts()->sync([$cart->id]);

        $voucher->refresh();
        $this->assertEquals(1, $voucher->carts()->count());
    }

    /** @test */
    public function by_cart_id()
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->create();

        /** @var Cart $cart */
        $cart = Cart::factory()->create();
        $voucher->carts()->sync([$cart->id]);

        $vouchers = Voucher::query()->byCartId($cart->id)->get();

        $this->assertEquals(1, $vouchers->count());
    }

    /** @test */
    public function find_valid_code()
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->amount(1000)->create();

        $result = Voucher::findByCode($voucher->code);
        $this->assertNotNull($result);
        $this->assertEquals($voucher->id, $result->getId());
    }

    /** @test */
    public function apply_code_to_cart()
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->amount(1000)->create();

        $cart = Cart::factory()->create();

        $voucher->applyToCart($cart);

        $count = Voucher::query()->byCartId($cart->id)->count();
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function find_unknown_code()
    {
        $result = Voucher::findByCode('TESTCODE');
        $this->assertNull($result);
    }

    /** @test */
    public function find_expired_code()
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->amount(1000)->expired(true)->create();

        $result = Voucher::findByCode($voucher->code);
        $this->assertNull($result);
    }

    /** @test */
    public function apply_unsupported_code_to_cart()
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->participants(5)->create();

        $cart = Cart::factory()->create();

        $this->expectException(UnsupportedVoucherTypeException::class);
        $this->expectExceptionMessage('Participants vouchers not supported yet.');

        $voucher->applyToCart($cart);
    }

    /** @test */
    public function apply_redeemed_code_to_cart()
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->amount(1000)->redeemed()->create();

        $cart = Cart::factory()->create();

        $this->expectException(VoucherRedeemedException::class);
        $this->expectExceptionMessage('Voucher already used.');

        $voucher->applyToCart($cart);
    }

    /** @test */
    public function get_codes_for_cart()
    {
        /** @var Voucher $voucher1 */
        $voucher1 = Voucher::factory()->amount(1000)->create();

        /** @var Voucher $voucher2 */
        $voucher2 = Voucher::factory()->amount(500)->create();

        $cart = Cart::factory()->create();
        $codes = Voucher::getCodesForCart($cart);
        $this->assertCount(0, $codes);

        $voucher1->applyToCart($cart);
        $voucher2->applyToCart($cart);

        $codes = Voucher::getCodesForCart($cart);

        $this->assertCount(2, $codes);
        $this->assertEquals($voucher1->code, $codes[0]->code);
        $this->assertEquals($voucher2->code, $codes[1]->code);
    }
}
