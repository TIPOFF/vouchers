<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Feature\Nova;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Tests\TestCase;

class VoucherResourceTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function index()
    {
        Voucher::factory()->count(4)->create();

        $this->actingAs(self::createPermissionedUser('view vouchers', true));

        $response = $this->getJson('nova-api/vouchers')
            ->assertOk();

        $this->assertCount(4, $response->json('resources'));
    }
}
