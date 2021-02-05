<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Models;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Models\VoucherType;
use Tipoff\Vouchers\Tests\TestCase;

class VoucherTypeModelTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function create()
    {
        /** @var VoucherType $voucherType */
        $voucherType = VoucherType::factory()->create();
        $this->assertNotNull($voucherType);
    }
}
