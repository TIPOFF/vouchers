<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Tipoff\Support\Models\TestModelStub;

class Location extends Model
{
    use TestModelStub;

    protected $guarded = [
        'id',
    ];
}
