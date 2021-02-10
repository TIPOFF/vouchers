<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Seeders;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\PermissionServiceProvider;
use Tipoff\Vouchers\Tests\TestCase;

class PermissionsSeederTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        $this->setUpTheTestEnvironment();

        if ($this->app->has(Permission::class)) {
            include_once __DIR__ . '/../../../vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php.stub';
            (new \CreatePermissionTables())->up();
        }

        parent::setUp();
    }

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
