<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Seeders;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tipoff\Vouchers\Database\Seeders\PermissionsSeeder;
use Tipoff\Vouchers\Tests\TestCase;

class PermissionsSeederTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function seed_with_no_table()
    {
        (new PermissionsSeeder())->run();

        $this->assertFalse(Schema::hasTable('permissions'));
    }

    /** @test */
    public function seed_with_table()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('guard_name');
            $table->timestamps();
        });

        $this->assertTrue(Schema::hasTable('permissions'));

        (new PermissionsSeeder())->run();

        $this->assertDatabaseCount('permissions', 7);
    }

    /** @test */
    public function seed_with_duplicates()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('guard_name');
            $table->timestamps();
        });

        (new PermissionsSeeder())->run();
        (new PermissionsSeeder())->run();

        $this->assertDatabaseCount('permissions', 7);
    }
}
