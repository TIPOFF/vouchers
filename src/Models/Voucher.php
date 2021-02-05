<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Models;

use Assert\Assert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tipoff\Checkout\Contracts\VouchersService;
use Tipoff\Checkout\Models\Cart;
use Tipoff\Checkout\Models\Order;
use Tipoff\Support\Models\BaseModel;

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
 */
class Voucher extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $casts = [
        'redeemed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($voucher) {
            if (empty($voucher->redeemable_at)) {
                $voucher->redeemable_at = Carbon::now()->addHours(24);
            }
            if (auth()->check()) {
                $voucher->creator_id = auth()->id();
            }
            $voucher->generateCode();
        });

        static::saving(function (Voucher $voucher) {
            $voucher->code = strtoupper($voucher->code);

            if (auth()->check()) {
                $voucher->updater_id = auth()->id();
            }
            if (empty($voucher->expires_at)) {
                $voucher->expires_at = Carbon::parse($voucher->created_at)->addDays($voucher->voucher_type->expiration_days);
            }

            Assert::lazy()
                ->that(empty($voucher->amount) && empty($voucher->participants))->false('A voucher must have either an amount or number of participants.')
                ->that(! empty($voucher->amount) && ! empty($voucher->participants))->false('A voucher cannot have both an amount & number of participants.')
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

        if (empty($this->redeemed_at)) {
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
        return $this->belongsTo(Order::class, 'purchase_order_id');
    }

    public function redemptionOrder()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function customer()
    {
        return $this->belongsTo(config('vouchers.model_class.customer'));
    }

    public function location()
    {
        return $this->belongsTo(config('vouchers.model_class.location'));
    }

    public function voucher_type()
    {
        return $this->belongsTo(VoucherType::class);
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class)->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(config('vouchers.model_class.user'), 'creator_id');
    }

    public function updater()
    {
        return $this->belongsTo(config('vouchers.model_class.user'), 'updater_id');
    }
}
