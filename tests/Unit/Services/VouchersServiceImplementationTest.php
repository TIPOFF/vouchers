<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Services;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Services\VouchersService;
use Tipoff\Vouchers\Tests\TestCase;

class VouchersServiceImplementationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function generate_code()
    {
        $voucher = Voucher::factory()->create();

        $api = $this->app->make(VouchersService::class);

        $code = $api->generateVoucherCode();
        $this->assertEquals(9, strlen($code));

        $this->assertNotEquals($voucher->code, $code);
        $this->assertEquals(substr($voucher->code, 0, 6), substr($code, 0, 6));
    }
}
