<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartVoucherPivotTable extends Migration
{
    public function up()
    {
        Schema::create('cart_voucher', function (Blueprint $table) {
            $table->integer('cart_id')->unsigned()->index();
            $table->integer('voucher_id')->unsigned()->index();
            $table->timestamps();
        });
    }
}
