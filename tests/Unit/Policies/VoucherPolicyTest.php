<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Policies;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Authorization\Models\User;
use Tipoff\Locations\Models\Location;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Tests\TestCase;

class VoucherPolicyTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function view_any()
    {
        $user = self::createPermissionedUser('view vouchers', true);
        $this->assertTrue($user->can('viewAny', Voucher::class));

        $user = self::createPermissionedUser('view vouchers', false);
        $this->assertTrue($user->can('viewAny', Voucher::class));
    }

    /**
     * @test
     * @dataProvider data_provider_for_all_permissions_as_creator
     */
    public function all_permissions_as_creator(string $action, ?string $permission, bool $expected)
    {
        $location = Location::factory()->create();
        $user = User::factory()->create();
        if ($permission) {
            $user->givePermissionTo($permission);
        }
        $user->locations()->attach($location);

        $voucher = Voucher::factory()->make([
            'user_id' => $user,
            'location_id' => $location,
        ]);

        $this->assertEquals($expected, $user->can($action, $voucher));
    }

    public function data_provider_for_all_permissions_as_creator()
    {
        return [
            'view-true' => [ 'view', 'view vouchers', true ],
            'view-false' => [ 'view', null, true ],
            'create-true' => [ 'create', 'create vouchers', true ],
            'create-false' => [ 'create', null, false ],
            'update-true' => [ 'update', 'update vouchers', true ],
            'update-false' => [ 'update', null, false ],
            'delete-false' => [ 'delete', null, false ],
        ];
    }

    /**
     * @test
     * @dataProvider data_provider_for_all_permissions_not_creator
     */
    public function all_permissions_not_creator(string $action, ?string $permission, bool $expected)
    {
        $location = Location::factory()->create();
        $user = User::factory()->create();
        if ($permission) {
            $user->givePermissionTo($permission);
        }
        $user->locations()->attach($location);

        $voucher = Voucher::factory()->make([
            'user_id' => User::factory()->create(),
            'location_id' => $location,
        ]);

        $this->assertEquals($expected, $user->can($action, $voucher));
    }

    public function data_provider_for_all_permissions_not_creator()
    {
        return [
            'view-true' => [ 'view', 'view vouchers', true ],
            'view-false' => [ 'view', null, false ],
            'create-true' => [ 'create', 'create vouchers', true ],
            'create-false' => [ 'create', null, false ],
            'update-true' => [ 'update', 'update vouchers', true ],
            'update-false' => [ 'update', null, false ],
            'delete-false' => [ 'delete', null, false ],
        ];
    }
}
