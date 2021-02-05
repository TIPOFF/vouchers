<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Support\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Resource;

class Location extends Resource
{
    public static $model = \Tipoff\Vouchers\Tests\Support\Models\Location::class;

    public function fields(Request $request)
    {
        // TODO: Implement fields() method.
    }
}
