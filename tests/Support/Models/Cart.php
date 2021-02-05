<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Tipoff\Support\Models\TestModelStub;
use Tipoff\Vouchers\Traits\HasCartVouchers;

class Cart extends Model
{
    use TestModelStub;
    use HasCartVouchers;

    protected $guarded = [
        'id',
    ];
}
