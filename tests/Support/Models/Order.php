<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Tipoff\Support\Models\TestModelStub;

class Order extends Model
{
    use TestModelStub;

    protected $guarded = [
        'id',
    ];
}
