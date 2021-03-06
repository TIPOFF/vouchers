<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\View\Components\Order;

use Illuminate\View\Component;
use Illuminate\View\View;
use Tipoff\Checkout\Models\OrderItem;
use Tipoff\Vouchers\Models\VoucherType;

class VoucherTypeComponent extends Component
{
    public OrderItem $orderItem;
    public VoucherType $sellable;

    public function __construct(OrderItem $orderItem, VoucherType $sellable)
    {
        $this->orderItem = $orderItem;
        $this->sellable = $sellable;
    }

    public function render()
    {
        /** @var View $view */
        $view = view('vouchers::components.order.voucher-type');

        return $view;
    }
}
