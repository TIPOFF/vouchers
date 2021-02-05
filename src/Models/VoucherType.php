<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Models;

use Assert\Assert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tipoff\Support\Models\BaseModel;

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
 */
class VoucherType extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

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
                $vouchertype->expiration_days = 365;
            }

            Assert::lazy()
                ->that(! empty($vouchertype->amount) && ! empty($vouchertype->participants))->false('A voucher cannot have both an amount & number of participants.')
                ->verifyNow();
        });
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    public function creator()
    {
        return $this->belongsTo(config('vouchers.model_class.user'), 'creator_id');
    }

    public function updater()
    {
        return $this->belongsTo(config('vouchers.model_class.user'), 'updater_id');
    }

    public function scopeIsSellable(Builder $query, bool $status = true): Builder
    {
        return $query->where('is_sellable', $status);
    }
}
