<?php

use Illuminate\Support\Facades\Route;
use Tipoff\Vouchers\Http\Controllers\Api\VoucherController;
use Tipoff\Vouchers\Http\Controllers\Api\VoucherTypeController;

Route::middleware(config('tipoff.api.middleware_group'))
    ->prefix(config('tipoff.api.uri_prefix'))
    ->group(function () {

    // PUBLIC ROUTES

    // PROTECTED ROUTES
    Route::middleware(config('tipoff.api.auth_middleware'))->group(function () {
        Route::resource('vouchers', VoucherController::class)->only('index', 'show');
        Route::resource('voucher-types', VoucherTypeController::class)->only('index', 'show');
    });
});
