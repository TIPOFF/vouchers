<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tipoff\Vouchers\Enums\VoucherSource;
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

        $source = $this->faker->randomElement(VoucherSource::getEnumerators());

        return [
            'code'              => $this->faker->md5,
            'user_id'           => randomOrCreate(app('user')),
            'location_id'       => randomOrCreate(app('location')),
            'source'            => $source,
            'voucher_type_id'   => $source->is(VoucherSource::PURCHASE()) ? randomOrCreate(VoucherType::class) : null,
            'purchase_order_id' => randomOrCreate(app('order')),
            'order_id'          => randomOrCreate(app('order')),
            'amount'            => $amount,
            'participants'      => $participants,
            'redeemed_at'       => $this->faker->dateTimeBetween($startDate = '-1 years', $endDate = '+1 years', $timezone = null),
            'creator_id'        => randomOrCreate(app('user')),
            'updater_id'        => randomOrCreate(app('user')),
        ];
    }

    public function redeemable(bool $isRedeemable = true): self
    {
        return $this->state(function (array $attributes) use ($isRedeemable) {
            return [
                'redeemed_at'   => null,
                'order_id'      => null,
                'expires_at'    => $this->faker->dateTimeBetween($startDate = '+1 month', $endDate = '+1 year', $timezone = null),
                'redeemable_at' => $isRedeemable
                    ? $this->faker->dateTimeBetween($startDate = '-1 year', $endDate = '-1 month', $timezone = null)
                    : $this->faker->dateTimeBetween($startDate = '+1 month', $endDate = '+1 year', $timezone = null),
            ];
        });
    }

    public function amount(?int $amount = null): self
    {
        return $this
            ->redeemable()
            ->state(function (array $attributes) use ($amount) {
                return [
                    'amount'            => $amount ?? $this->faker->numberBetween(100, 1000),
                    'participants'      => null,
                ];
            });
    }

    public function participants(?int $participants = null): self
    {
        return $this
            ->redeemable()
            ->state(function (array $attributes) use ($participants) {
                return [
                    'amount'            => null,
                    'participants'      => $participants ?? $this->faker->numberBetween(1, 10),
                ];
            });
    }

    public function redeemed(bool $isRedeemed = true): self
    {
        return $this->state(function (array $attributes) use ($isRedeemed) {
            return [
                'order_id'    => $isRedeemed ? randomOrCreate(app('order')) : null,
                'redeemed_at' => $isRedeemed
                    ? $this->faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now', $timezone = null)
                    : null,
            ];
        });
    }

    public function expired(bool $isExpired = true): self
    {
        return $this->state(function (array $attributes) use ($isExpired) {
            return [
                'expires_at' => $isExpired
                    ? $this->faker->dateTimeBetween($startDate = '-1 year', $endDate = '-1 month', $timezone = null)
                    : $this->faker->dateTimeBetween($startDate = '+1 month', $endDate = '+1 year', $timezone = null),
            ];
        });
    }

    public function source(VoucherSource $source): self
    {
        return $this->state(function (array $attributes) use ($source) {
            return [
                'source'            => $source,
                'voucher_type_id'   => $source->is(VoucherSource::PURCHASE()) ? randomOrCreate(VoucherType::class) : null,
            ];
        });
    }
}
