<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Feature\Nova;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Authorization\Models\User;
use Tipoff\Vouchers\Models\VoucherType;
use Tipoff\Vouchers\Tests\TestCase;

class VoucherTypeResourceTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function index()
    {
        VoucherType::factory()->count(4)->create();

        $this->actingAs(self::createPermissionedUser('view voucher types', true));

        $response = $this->getJson('nova-api/voucher-types')
            ->assertOk();

        $this->assertCount(4, $response->json('resources'));
    }
}
