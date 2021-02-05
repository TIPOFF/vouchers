<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tipoff\Vouchers\Models\VoucherType;

class VoucherTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = VoucherType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $sentence = $this->faker->unique()->sentence;
        if ($this->faker->boolean) {
            $amount = $this->faker->numberBetween(100, 1000);
            $participants = null;
        } else {
            $amount = null;
            $participants = $this->faker->numberBetween(1, 10);
        }

        return [
            'name'          => $sentence,
            'slug'          => Str::slug($sentence),
            'title'         => $sentence,
            'amount'        => $amount,
            'participants'  => $participants,
            'is_sellable'   => $this->faker->boolean,
            'creator_id'    => randomOrCreate(config('vouchers.model_class.user')),
            'updater_id'    => randomOrCreate(config('vouchers.model_class.user')),
        ];
    }
}
