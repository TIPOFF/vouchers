<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Seeders;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tipoff\Vouchers\Database\Seeders\PermissionsSeeder;
use Tipoff\Vouchers\Tests\TestCase;

class PermissionsSeederTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        include_once __DIR__ . '/../../../vendor/spatie/laravel-permission/database/migrations/create_permission_tables.php.stub';
        (new \CreatePermissionTables())->up();
    }

    /** @test */
    public function seed_with_table()
    {
        $this->assertTrue(Schema::hasTable('permissions'));

        (new PermissionsSeeder())->run();

        $this->assertDatabaseCount('permissions', 7);
    }

    /** @test */
    public function seed_with_duplicates()
    {
        (new PermissionsSeeder())->run();
        (new PermissionsSeeder())->run();

        $this->assertDatabaseCount('permissions', 7);
    }
}
