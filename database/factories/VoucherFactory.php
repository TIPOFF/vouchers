<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Models\VoucherType;

class VoucherFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Voucher::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        if ($this->faker->boolean) {
            $amount = $this->faker->numberBetween(100, 1000);
            $participants = null;
        } else {
            $amount = null;
            $participants = $this->faker->numberBetween(1, 10);
        }

        return [
            'code'              => $this->faker->md5,
            'customer_id'       => randomOrCreate(config('vouchers.model_class.customer')),
            'location_id'       => randomOrCreate(config('vouchers.model_class.location')),
            'voucher_type_id'   => randomOrCreate(VoucherType::class),
            'purchase_order_id' => randomOrCreate(config('vouchers.model_class.order')),
            'order_id'          => randomOrCreate(config('vouchers.model_class.order')),
            'amount'            => $amount,
            'participants'      => $participants,
            'redeemed_at'       => $this->faker->dateTimeBetween($startDate = '-1 years', $endDate = '+1 years', $timezone = null),
            'creator_id'        => randomOrCreate(config('vouchers.model_class.user')),
            'updater_id'        => randomOrCreate(config('vouchers.model_class.user')),
        ];
    }
}
