<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Policies;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Tests\TestCase;
use Tipoff\Support\Contracts\Models\UserInterface;

class VoucherPolicyTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function view_any()
    {
        $user = self::createPermissionedUser('view vouchers', true);
        $this->assertTrue($user->can('viewAny', Voucher::class));

        $user = self::createPermissionedUser('view vouchers', false);
        $this->assertFalse($user->can('viewAny', Voucher::class));
    }

    /**
     * @test
     * @dataProvider data_provider_for_all_permissions_as_creator
     */
    public function all_permissions_as_creator(string $permission, UserInterface $user, bool $expected)
    {
        $discount = Voucher::factory()->make([
            'creator_id' => $user,
        ]);

        $this->assertEquals($expected, $user->can($permission, $discount));
    }

    public function data_provider_for_all_permissions_as_creator()
    {
        return [
            'view-true' => [ 'view', self::createPermissionedUser('view vouchers', true), true ],
            'view-false' => [ 'view', self::createPermissionedUser('view vouchers', false), false ],
            'create-true' => [ 'create', self::createPermissionedUser('create vouchers', true), true ],
            'create-false' => [ 'create', self::createPermissionedUser('create vouchers', false), false ],
            'update-true' => [ 'update', self::createPermissionedUser('update vouchers', true), true ],
            'update-false' => [ 'update', self::createPermissionedUser('update vouchers', false), false ],
            'delete-true' => [ 'delete', self::createPermissionedUser('delete vouchers', true), false ],
            'delete-false' => [ 'delete', self::createPermissionedUser('delete vouchers', false), false ],
        ];
    }

    /**
     * @test
     * @dataProvider data_provider_for_all_permissions_not_creator
     */
    public function all_permissions_not_creator(string $permission, UserInterface $user, bool $expected)
    {
        $discount = Voucher::factory()->make();

        $this->assertEquals($expected, $user->can($permission, $discount));
    }

    public function data_provider_for_all_permissions_not_creator()
    {
        // Permissions are identical for creator or others
        return $this->data_provider_for_all_permissions_as_creator();
    }
}
