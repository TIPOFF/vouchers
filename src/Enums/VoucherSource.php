<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Enums;

use Tipoff\Support\Enums\BaseEnum;

/**
 * @method static VoucherSource REFUND()
 * @method static VoucherSource PARTIAL_REDEMPTION()
 * @method static VoucherSource PURCHASE()
 * @psalm-immutable
 */
class VoucherSource extends BaseEnum
{
    const REFUND             = 'refund';
    const PARTIAL_REDEMPTION = 'partial_redemption';
    const PURCHASE           = 'purchase';
}
