<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Http\Controllers\Api;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpFoundation\Response;
use Tipoff\Authorization\Models\User;
use Tipoff\Vouchers\Enums\VoucherSource;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Tests\TestCase;

class VoucherControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function index()
    {
        // First user
        $user = User::factory()->create();
        Voucher::factory()->count(4)->create([
            'user_id' => $user,
        ]);

        // Second user
        $user = User::factory()->create();
        Voucher::factory()->count(4)->create([
            'user_id' => $user,
        ]);

        $this->actingAs($user);

        $response = $this->getJson($this->apiUrl('vouchers'))
            ->assertOk();

        $this->assertCount(4, $response->json('data'));
    }

    /** @test */
    public function show_voucher_i_own()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $voucher = Voucher::factory()->create([
            'user_id' => $user,
        ]);

        $this->actingAs($user);

        $response = $this->getJson($this->apiUrl("vouchers/{$voucher->id}"))
            ->assertOk();

        $this->assertEquals($voucher->code, $response->json('data.code'));
        $this->assertNull($response->json('data.voucherType'));
    }

    /** @test */
    public function show_voucher_i_own_with_type()
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->source(VoucherSource::PURCHASE())->create([
            'user_id' => $user,
        ]);

        $this->actingAs($user);

        $response = $this->getJson($this->apiUrl("vouchers/{$voucher->id}?include=voucherType"))
            ->assertOk();

        $this->assertEquals($voucher->code, $response->json('data.code'));
        $this->assertNotNull($response->json('data.voucherType'));
        $this->assertEquals($voucher->voucher_type->name, $response->json('data.voucherType.data.name'));
    }

    /** @test */
    public function show_voucher_i_dont_own()
    {
        $user = User::factory()->create();
        $voucher = Voucher::factory()->create([
            'user_id' => $user,
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->getJson($this->apiUrl("vouchers/{$voucher->id}"))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function index_not_logged_in()
    {
        $this->getJson($this->apiUrl('vouchers'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function show_not_logged_in()
    {
        $voucher = Voucher::factory()->create();

        $this->getJson($this->apiUrl("vouchers/{$voucher->id}"))
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }
}
