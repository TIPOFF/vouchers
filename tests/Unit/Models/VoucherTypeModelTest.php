<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Models;

use Assert\LazyAssertionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Authorization\Models\User;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Models\VoucherType;
use Tipoff\Vouchers\Tests\TestCase;

class VoucherTypeModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function create()
    {
        /** @var VoucherType $voucherType */
        $voucherType = VoucherType::factory()->create();
        $this->assertNotNull($voucherType);
    }

    /** @test */
    public function creator_and_updater_are_set()
    {
        $creator = User::factory()->create();
        $this->actingAs($creator);

        $voucherType = VoucherType::factory()->create([
            'creator_id' => null,
            'updater_id' => null,
        ]);

        $this->assertNotNull($voucherType->creator_id);
        $this->assertEquals($creator->id, $voucherType->creator_id);
        $this->assertInstanceOf(Model::class, $voucherType->creator);

        $this->assertNotNull($voucherType->updater_id);
        $this->assertEquals($creator->id, $voucherType->updater_id);
        $this->assertInstanceOf(Model::class, $voucherType->updater);
    }

    /** @test */
    public function updater_is_set()
    {
        $creator = User::factory()->create();
        $voucherType = VoucherType::factory()->create([
            'name' => 'ABCD',
            'creator_id' => $creator,
            'updater_id' => $creator,
        ]);

        $updater = User::factory()->create();
        $this->assertNotEquals($creator->id, $updater->id);

        $this->actingAs($updater);

        $voucherType->name = 'HIJK';
        $voucherType->save();

        $this->assertEquals($updater->id, $voucherType->updater_id);
        $this->assertInstanceOf(Model::class, $voucherType->updater);
    }

    /** @test */
    public function default_expiration_days()
    {
        /** @var VoucherType $voucherType */
        $voucherType = VoucherType::factory()->create([
            'expiration_days' => null,
        ]);

        $this->assertEquals(config('vouchers.default_expiration_days'), $voucherType->expiration_days);
    }

    /** @test */
    public function is_sellable()
    {
        VoucherType::factory()->count(4)->create([
            'is_sellable' => false,
        ]);

        $this->assertEquals(0, VoucherType::query()->isSellable(true)->count());
        $this->assertEquals(4, VoucherType::query()->isSellable(false)->count());

        VoucherType::factory()->count(3)->create([
            'is_sellable' => true,
        ]);

        $this->assertEquals(3, VoucherType::query()->isSellable(true)->count());
        $this->assertEquals(4, VoucherType::query()->isSellable(false)->count());
    }

    /** @test */
    public function vouchers()
    {
        /** @var VoucherType $voucherType */
        $voucherType = VoucherType::factory()->create();

        Voucher::factory()->count(3)->create([
            'voucher_type_id' => $voucherType,
        ]);

        $voucherType->refresh();

        $this->assertCount(3, $voucherType->vouchers);

        Voucher::factory()->count(2)->create([
            'voucher_type_id' => $voucherType,
        ]);

        $voucherType->refresh();

        $this->assertCount(5, $voucherType->vouchers);
    }

    /** @test */
    public function both_amount_and_participants()
    {
        $this->expectException(LazyAssertionException::class);
        $this->expectExceptionMessage('amount: A voucher cannot have both an amount & number of participants.');

        VoucherType::factory()->create([
            'amount' => 4,
            'participants' => 5,
        ]);
    }
}
