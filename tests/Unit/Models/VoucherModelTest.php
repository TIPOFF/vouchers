<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Models;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Tests\TestCase;

class VoucherModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function create()
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->create();
        $this->assertNotNull($voucher);
    }
}
