<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tipoff\Vouchers\Models\VoucherType;

class VoucherTypeFactory extends Factory
{
    protected $model = VoucherType::class;

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
            'creator_id'    => randomOrCreate(app('user')),
            'updater_id'    => randomOrCreate(app('user')),
        ];
    }

    public function amount(?int $amount): self
    {
        return $this
            ->state(function (array $attributes) use ($amount) {
                return [
                    'amount'            => $amount ?? $this->faker->numberBetween(100, 1000),
                    'participants'      => null,
                ];
            });
    }

    public function participants(?int $participants): self
    {
        return $this
            ->state(function (array $attributes) use ($participants) {
                return [
                    'amount'            => null,
                    'participants'      => $participants ?? $this->faker->numberBetween(1, 10),
                ];
            });
    }

    public function sellable(bool $sellable = true): self
    {
        return $this
            ->state(function (array $attributes) use ($sellable) {
                return [
                    'is_sellable' => $sellable,
                ];
            });
    }


}
