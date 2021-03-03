<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use Tipoff\Checkout\Models\CartItem;
use Tipoff\Vouchers\Models\VoucherType;

class VoucherTypeComponent extends Component
{
    public CartItem $cartItem;
    public VoucherType $sellable;

    public function __construct(CartItem $cartItem, VoucherType $sellable)
    {
        $this->cartItem = $cartItem;
        $this->sellable = $sellable;
    }

    public function render()
    {
        /** @var View $view */
        $view = view('components.voucher-type');

        return $view;
    }
}
