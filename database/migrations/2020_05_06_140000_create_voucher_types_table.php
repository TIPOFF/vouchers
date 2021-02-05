<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherTypesTable extends Migration
{
    public function up()
    {
        Schema::create('voucher_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Internal reference name
            $table->string('title'); // Shows in gift certificate purchase flow
            $table->string('slug')->unique()->index();
            $table->boolean('is_sellable')->default(false);
            $table->unsignedInteger('sell_price')->nullable(); // Sell price is in cents. Null allows custom amounts and sell price will be same as amount.

            $table->unsignedInteger('amount')->nullable(); // Amount is in cents. Value of the voucher.
            $table->unsignedTinyInteger('participants')->nullable();
            $table->unsignedSmallInteger('expiration_days'); // Number of days where this type of voucher will expire. Used to compute the expires_at field on the voucher.

            $table->foreignIdFor(config('discounts.model_class.user'), 'creator_id');
            $table->foreignIdFor(config('discounts.model_class.user'), 'updater_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
