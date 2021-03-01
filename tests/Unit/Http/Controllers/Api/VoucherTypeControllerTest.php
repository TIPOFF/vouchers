<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Http\Controllers\Api;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\TestSupport\Models\User;
use Tipoff\Vouchers\Models\VoucherType;
use Tipoff\Vouchers\Tests\TestCase;

class VoucherTypeControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function index()
    {
        /** @var User $user */
        $user = User::factory()->create();

        VoucherType::factory()->sellable(false)->count(2)->create();

        $this->actingAs($user->removePermissions());
        $response = $this->getJson('tipoff/voucher-types')
            ->assertOk();

        $this->assertCount(0, $response->json('data'));

        VoucherType::factory()->sellable(true)->count(2)->create();
        $response = $this->getJson('tipoff/voucher-types')
            ->assertOk();

        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function show_non_sellable_voucher_type()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $voucherType = VoucherType::factory()->sellable(false)->create();

        $this->actingAs($user->removePermissions());

        $this->getJson("tipoff/voucher-types/{$voucherType->id}")
            ->assertStatus(403);
    }

    /** @test */
    public function show_sellable_voucher_type()
    {
        /** @var User $user */
        $user = User::factory()->create();

        /** @var VoucherType $voucherType */
        $voucherType = VoucherType::factory()->sellable()->create();

        $this->actingAs($user->removePermissions());

        $response = $this->getJson("tipoff/voucher-types/{$voucherType->id}")
            ->assertOk();

        $this->assertEquals($voucherType->id, $response->json('data.id'));
    }

    /** @test */
    public function index_not_logged_in()
    {
        $this->getJson('tipoff/voucher-types')
            ->assertStatus(401);
    }

    /** @test */
    public function show_not_logged_in()
    {
        /** @var VoucherType $voucherType */
        $voucherType = VoucherType::factory()->sellable()->create();

        $this->getJson("tipoff/voucher-types/{$voucherType->id}")
            ->assertStatus(401);
    }
}
