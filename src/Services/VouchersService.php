<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Tipoff\Vouchers\Models\Voucher;

class VouchersService
{
    public function generateVoucherCode(): string
    {
        do {
            $code = Carbon::now('America/New_York')->format('ymd').Str::upper(Str::random(3));
        } while (Voucher::where('code', $code)->first());

        return $code;
    }
}
