<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Migrations;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionServiceProvider;
use Tipoff\Vouchers\Tests\TestCase;

class PermissionsMigrationsTest extends TestCase
{
    use DatabaseTransactions;

    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            PermissionServiceProvider::class,
        ]);
    }

    /** @test */
    public function permissions_seeded()
    {
        $this->assertTrue(Schema::hasTable('permissions'));
        $this->assertDatabaseCount('permissions', 7);
    }
}
