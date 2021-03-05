<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\View\Components\Cart;

use Illuminate\View\View;
use Tipoff\Checkout\View\Components\BaseDeductionComponent;
use Tipoff\Vouchers\Models\Voucher;

class VoucherComponent extends BaseDeductionComponent
{
    public Voucher $voucher;

    public function __construct(Voucher $deduction)
    {
        parent::__construct($deduction);
        $this->voucher = $deduction;
    }

    public function render()
    {
        /** @var View $view */
        $view = view('vouchers::components.cart.voucher');

        return $view;
    }
}
