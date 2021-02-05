<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Support\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Resource;

class User extends Resource
{
    public static $model = \Tipoff\Vouchers\Tests\Support\Models\User::class;

    public function fields(Request $request)
    {
        // TODO: Implement fields() method.
    }
}
