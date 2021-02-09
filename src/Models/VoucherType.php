<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Models;

use Assert\Assert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tipoff\Support\Models\BaseModel;
use Tipoff\Support\Traits\HasPackageFactory;

/**
 * @property int id
 * @property string name
 * @property string title
 * @property string slug
 * @property bool is_sellable
 * @property int sell_price
 * @property int amount
 * @property int participants
 * @property int expiration_days
 * @property Carbon created_at
 * @property Carbon updated_at
 * // Raw Relations
 * @property int creator_id
 * @property int updater_id
 */
class VoucherType extends BaseModel
{
    use HasPackageFactory;
    use SoftDeletes;

    const DEFAULT_EXPIRATION_DAYS = 365;

    protected $guarded = ['id'];


    protected $casts = [
        'is_sellable' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vouchertype) {
            if (auth()->check()) {
                $vouchertype->creator_id = auth()->id();
            }
        });

        static::saving(function ($vouchertype) {
            if (auth()->check()) {
                $vouchertype->updater_id = auth()->id();
            }
            if (empty($vouchertype->expiration_days)) {
                $vouchertype->expiration_days = self::DEFAULT_EXPIRATION_DAYS;
            }

            Assert::lazy()
                ->that(! empty($vouchertype->amount) && ! empty($vouchertype->participants), 'amount')->false('A voucher cannot have both an amount & number of participants.')
                ->verifyNow();
        });
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function creator()
    {
        return $this->belongsTo(app('user'), 'creator_id');
    }

    public function updater()
    {
        return $this->belongsTo(app('user'), 'updater_id');
    }

    public function scopeIsSellable(Builder $query, bool $status = true): Builder
    {
        return $query->where('is_sellable', $status);
    }
}
