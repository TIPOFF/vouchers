<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Commands;

use Illuminate\Console\Command;
use Tipoff\Vouchers\Models\Voucher;

class VouchersValidate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vouchers:validate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark used vouchers as redeemed.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Voucher::whereHas('carts')->each(function ($voucher) {
            if (! empty($voucher->redeemed_at)) {
                return;
            }

            if (empty($voucher->carts()->first()->order_id)) {
                return;
            }

            $voucher
                ->redeem()
                ->save();
        });
    }
}
