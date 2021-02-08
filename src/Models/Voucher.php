<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tipoff\Support\Traits\HasPackageFactory;
use Tipoff\Vouchers\Services\VouchersService;

class Voucher extends Model
{
    use HasPackageFactory;
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

        static::saving(function ($voucher) {
            $voucher->code = strtoupper($voucher->code);

            if (auth()->check()) {
                $voucher->updater_id = auth()->id();
            }
            if (empty($voucher->expires_at)) {
                $voucher->expires_at = Carbon::parse($voucher->created_at)->addDays($voucher->voucher_type->expiration_days);
            }
            if (empty($voucher->amount) && empty($voucher->participants)) {
                throw new \Exception('A voucher must have either an amount or number of participants.');
            }
            if (! empty($voucher->amount) && ! empty($voucher->participants)) {
                throw new \Exception('A voucher cannot have both an amount & number of participants.');
            }
        });
    }

    /**
     * Generate random voucher code.
     *
     * @return self
     */
    public function generateCode()
    {
        $this->code = app(VouchersService::class)->generateVoucherCode();
    }

    /**
     * Scope vouchers to valid ones.
     *
     * @param Builder $query
     * @param string|Carbon $date
     * @return Builder
     */
    public function scopeValidAt($query, $date)
    {
        return $query
            ->whereDate('expires_at', '>=', $date)
            ->where('redeemable_at', '<=', $date)
            ->whereNull('redeemed_at');
    }

    /**
     * Reset voucher.
     *
     * @return self
     */
    public function reset()
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
    public function isValidAt($date)
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
    public function decoratedAmount()
    {
        return '$' . number_format($this->amount / 100, 2, '.', ',');
    }

    /**
     * Mark voucher as redeemed.
     *
     * @return self
     */
    public function redeem()
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

    public function creator()
    {
        return $this->belongsTo(app('user'), 'creator_id');
    }

    public function updater()
    {
        return $this->belongsTo(app('user'), 'updater_id');
    }
}
