<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tipoff\Support\Traits\HasPackageFactory;

class VoucherType extends Model
{
    use HasPackageFactory;
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
            if (! empty($vouchertype->amount) && ! empty($vouchertype->participants)) {
                throw new \Exception('A voucher cannot have both an amount & number of participants.');
            }
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

    public function scopeIsSellable($query, $status = true)
    {
        return $query->where('is_sellable', $status);
    }
}
