<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Policies;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Support\Contracts\Models\UserInterface;
use Tipoff\Vouchers\Models\VoucherType;
use Tipoff\Vouchers\Tests\TestCase;

class VoucherTypePolicyTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function view_any()
    {
        $user = self::createPermissionedUser('view voucher types', true);
        $this->assertTrue($user->can('viewAny', VoucherType::class));

        $user = self::createPermissionedUser('view voucher types', false);
        $this->assertTrue($user->can('viewAny', VoucherType::class));
    }

    /**
     * @test
     * @dataProvider data_provider_for_all_permissions_as_creator
     */
    public function all_permissions_as_creator(string $permission, UserInterface $user, bool $expected)
    {
        $discount = VoucherType::factory()->sellable()->make([
            'creator_id' => $user,
        ]);

        $this->assertEquals($expected, $user->can($permission, $discount));
    }

    public function data_provider_for_all_permissions_as_creator()
    {
        return [
            'view-true' => [ 'view', self::createPermissionedUser('view voucher types', true), true ],
            'view-false' => [ 'view', self::createPermissionedUser('view voucher types', false), true ],
            'create-true' => [ 'create', self::createPermissionedUser('create voucher types', true), true ],
            'create-false' => [ 'create', self::createPermissionedUser('create voucher types', false), false ],
            'update-true' => [ 'update', self::createPermissionedUser('update voucher types', true), true ],
            'update-false' => [ 'update', self::createPermissionedUser('update voucher types', false), false ],
            'delete-true' => [ 'delete', self::createPermissionedUser('delete voucher types', true), true ],
            'delete-false' => [ 'delete', self::createPermissionedUser('delete voucher types', false), false ],
        ];
    }

    /**
     * @test
     * @dataProvider data_provider_for_all_permissions_not_creator
     */
    public function all_permissions_not_creator(string $permission, UserInterface $user, bool $expected)
    {
        $discount = VoucherType::factory()->sellable()->make();

        $this->assertEquals($expected, $user->can($permission, $discount));
    }

    public function data_provider_for_all_permissions_not_creator()
    {
        // Permissions are identical for creator or others
        return $this->data_provider_for_all_permissions_as_creator();
    }
}
