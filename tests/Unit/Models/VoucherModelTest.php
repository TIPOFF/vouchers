<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Models;

use Assert\LazyAssertionException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\TestSupport\Models\User;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Models\VoucherType;
use Tipoff\Vouchers\Services\VouchersService;
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
        $vouchersService = \Mockery::mock(VouchersService::class);
        $vouchersService
            ->shouldReceive('generateVoucherCode')
            ->twice()
            ->andReturn('abcd');

        $this->app->instance(VouchersService::class, $vouchersService);

        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->create();

        $voucher->generateCode()->save();

        $this->assertEquals('ABCD', $voucher->code);
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

    /** @test */
    public function reset()
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()
            ->create([
                'redeemed_at' => Carbon::now(),
                'purchase_order_id' => randomOrCreate(app('order')),
                'order_id' => randomOrCreate(app('order')),
            ]);

        app('cart')::factory()
            ->count(5)
            ->create()
            ->each(function ($cart) use ($voucher) {
                $voucher->carts()->attach($cart->id);
            });

        $voucher->refresh();

        $this->assertCount(5, $voucher->carts);
        $this->assertNotNull($voucher->redeemed_at);
        $this->assertNotNull($voucher->order);
        $this->assertNotNull($voucher->purchaseOrder);

        $voucher->reset()->refresh();

        $this->assertCount(0, $voucher->carts);
        $this->assertNull($voucher->redeemed_at);
        $this->assertNull($voucher->order);
        $this->assertNull($voucher->purchaseOrder);
    }

    /**
     * @test
     * @dataProvider data_provider_for_is_valid_at
     */
    public function is_valid_at(Carbon $expiresAt, Carbon $redeemableAt, ?Carbon $redeemedAt, bool $expected)
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()
            ->create([
                'expires_at' => $expiresAt,
                'redeemable_at' => $redeemableAt,
                'redeemed_at' => $redeemedAt,
            ]);

        $this->assertEquals($expected, $voucher->isValidAt(Carbon::now()));
    }

    public function data_provider_for_is_valid_at()
    {
        return [
            'valid' => [ Carbon::now()->addDay(), Carbon::now()->subDay(), null, true ],
            'expired' => [ Carbon::now()->subDay(), Carbon::now()->subDay(), null, false ],
            'not_redeemable' => [ Carbon::now()->addDay(), Carbon::now()->addDay(), null, false ],
            'redeemed' => [ Carbon::now()->addDay(), Carbon::now()->subDay(), Carbon::now(), false ],
            'all_reasons' => [ Carbon::now()->subDay(), Carbon::now()->addDay(), Carbon::now(), false ],
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
            $voucher = Voucher::factory()
                ->create([
                    'redeemed_at' => null,
                ]);

            $this->assertNull($voucher->redeemed_at);

            $voucher->redeem();

            $this->assertNotNull($voucher->redeemed_at);
            $this->assertEquals($now->getTimestamp(), $voucher->redeemed_at->getTimestamp());
        } finally {
            Carbon::setTestNow(null);
        }
    }
}
