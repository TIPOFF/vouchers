<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Http\Controllers\Api;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Addresses\Models\Customer;
use Tipoff\TestSupport\Models\User;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Tests\TestCase;

class VoucherControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function index()
    {
        // First customer/user
        $customer = Customer::factory()->create([
            'user_id' => User::factory()->create(),
        ]);
        Voucher::factory()->count(4)->create([
            'customer_id' => $customer,
        ]);

        // Second customer/user
        $customer = Customer::factory()->create([
            'user_id' => User::factory()->create(),
        ]);
        Voucher::factory()->count(4)->create([
            'customer_id' => $customer,
        ]);

        $this->actingAs($customer->user);

        $response = $this->getJson('tipoff/vouchers')
            ->assertOk();

        $this->assertCount(4, $response->json('data'));
    }

    /** @test */
    public function show_voucher_i_own()
    {
        $this->logToStderr();
        /** @var User $user */
        $user = User::factory()->create();

        // First customer/user
        $customer = Customer::factory()->create([
            'user_id' => $user,
        ]);
        $voucher = Voucher::factory()->create([
            'customer_id' => $customer,
        ]);

        $this->actingAs($user->removePermissions());

        $response = $this->getJson("tipoff/vouchers/{$voucher->id}")
            ->assertOk();

        $this->assertEquals($voucher->code, $response->json('data.code'));
    }

    /** @test */
    public function show_voucher_i_dont_own()
    {
        // First customer/user
        $customer = Customer::factory()->create();
        $voucher = Voucher::factory()->create([
            'customer_id' => $customer,
        ]);

        $user = self::createPermissionedUser('view vouchers', false);
        $this->actingAs($user);

        $this->getJson("tipoff/vouchers/{$voucher->id}")
            ->assertStatus(403);
    }

    /** @test */
    public function index_not_logged_in()
    {
        $this->getJson('tipoff/vouchers')
            ->assertStatus(401);
    }

    /** @test */
    public function show_not_logged_in()
    {
        $voucher = Voucher::factory()->create();

        $this->getJson("tipoff/vouchers/{$voucher->id}")
            ->assertStatus(401);
    }
}
