<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Models;

use Assert\Assert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tipoff\Support\Models\BaseModel;
use Tipoff\Support\Traits\HasCreator;
use Tipoff\Support\Traits\HasPackageFactory;
use Tipoff\Support\Traits\HasUpdater;
use Tipoff\Vouchers\Services\VouchersService;

/**
 * @property int id
 * @property string code
 * @property int amount
 * @property int participants
 * @property Carbon redeemable_at
 * @property Carbon redeemed_at
 * @property Carbon expires_at
 * @property Carbon created_at
 * @property Carbon updated_at
 * // Raw Relations
 * @property int voucher_type_id
 * @property int order_id
 * @property int purchase_order_id
 * @property int customer_id
 * @property int location_id
 * @property int creator_id
 * @property int updater_id
 */
class Voucher extends BaseModel
{
    use HasPackageFactory;
    use HasCreator;
    use HasUpdater;
    use SoftDeletes;

    const DEFAULT_REDEEMABLE_HOURS = 24;

    protected $casts = [
        'id' => 'integer',
        'amount' => 'integer',
        'participants' => 'integer',
        'redeemed_at' => 'datetime',
        'expires_at' => 'datetime',
        'voucher_type_id' => 'integer',
        'order_id' => 'integer',
        'purchase_order_id' => 'integer',
        'customer_id' => 'integer',
        'location_id' => 'integer',
        'creator_id' => 'integer',
        'updater_id' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($voucher) {
            if (empty($voucher->redeemable_at)) {
                $voucher->redeemable_at = Carbon::now()->addHours(self::DEFAULT_REDEEMABLE_HOURS);
            }
            $voucher->generateCode();
        });

        static::saving(function (Voucher $voucher) {
            $voucher->code = strtoupper($voucher->code);

            if (empty($voucher->expires_at)) {
                $voucher->expires_at = Carbon::parse($voucher->created_at)->addDays($voucher->voucher_type->expiration_days);
            }

            Assert::lazy()
                ->that(empty($voucher->amount) && empty($voucher->participants), 'amount')->false('A voucher must have either an amount or number of participants.')
                ->that(! empty($voucher->amount) && ! empty($voucher->participants), 'amount')->false('A voucher cannot have both an amount & number of participants.')
                ->verifyNow();
        });
    }

    /**
     * Generate random voucher code.
     *
     * @return self
     */
    public function generateCode(): self
    {
        $this->code = app(VouchersService::class)->generateVoucherCode();

        return $this;
    }

    /**
     * Scope vouchers to valid ones.
     *
     * @param Builder $query
     * @param string|Carbon $date
     * @return Builder
     */
    public function scopeValidAt(Builder $query, $date): Builder
    {
        return $query
            ->whereDate('expires_at', '>=', $date)
            ->where('redeemable_at', '<=', $date)
            ->whereNull('redeemed_at');
    }

    public function scopeByCartId(Builder $query, int $cartId): Builder
    {
        return $query->whereHas('carts', function ($q) use ($cartId) {
            $q->where('id', $cartId);
        });
    }

    /**
     * Reset voucher.
     *
     * @return self
     */
    public function reset(): self
    {
        $this->redeemed_at = null;
        $this->purchase_order_id = null;
        $this->order_id = null;

        $this->carts()->sync([]);

        $this->save();

        return $this;
    }

    /**
     * Validate is current voucher is available at specified date.
     *
     * @param string|Carbon $date
     * @return bool
     */
    public function isValidAt($date): bool
    {
        if (! $date instanceof Carbon) {
            $date = new Carbon($date);
        }

        if ($date->gt($this->expires_at)) {
            return false;
        }

        if ($date->lt($this->redeemable_at)) {
            return false;
        }

        if (! empty($this->redeemed_at)) {
            return false;
        }

        return true;
    }

    /**
     * Generate formated amount.
     *
     * @return string
     */
    public function decoratedAmount(): string
    {
        return '$' . number_format($this->amount / 100, 2, '.', ',');
    }

    /**
     * Mark voucher as redeemed.
     *
     * @return self
     */
    public function redeem(): self
    {
        $this->redeemed_at = Carbon::now();

        return $this;
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(app('order'), 'purchase_order_id');
    }

    public function redemptionOrder()
    {
        return $this->belongsTo(app('order'), 'order_id');
    }

    public function order()
    {
        return $this->belongsTo(app('order'), 'order_id');
    }

    public function customer()
    {
        return $this->belongsTo(app('customer'));
    }

    public function location()
    {
        return $this->belongsTo(app('location'));
    }

    public function voucher_type()
    {
        return $this->belongsTo(VoucherType::class);
    }

    public function carts()
    {
        return $this->belongsToMany(app('cart'))->withTimestamps();
    }
}
