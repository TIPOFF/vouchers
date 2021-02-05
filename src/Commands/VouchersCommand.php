<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Commands;

use Illuminate\Console\Command;

class VouchersCommand extends Command
{
    public $signature = 'vouchers';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
