<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\View\Components;

use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Tests\TestCase;

class VoucherComponentTest extends TestCase
{
    /** @test */
    public function single_adjustment()
    {
        $voucher = Voucher::factory()->amount(1234)->create();

        $view = $this->blade(
            '<x-tipoff-cart-deductions :deductions="$deductions" />',
            ['deductions' => [
                $voucher,
            ]]
        );

        $view->assertSee($voucher->code);
        $view->assertSee('Voucher: $12.34');
    }

    /** @test */
    public function multiple_adjustments()
    {
        $voucher1 = Voucher::factory()->amount(123)->create();
        $voucher2 = Voucher::factory()->amount(234)->create();

        $view = $this->blade(
            '<x-tipoff-cart-deductions :deductions="$deductions" />',
            ['deductions' => [
                $voucher1,
                $voucher2,
            ]]
        );

        $view->assertSee($voucher1->code);
        $view->assertSee('Voucher: $1.23');
        $view->assertSee($voucher2->code);
        $view->assertSee('Voucher: $2.34');
    }
}
