<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Models;

use Assert\Assert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tipoff\Support\Contracts\Checkout\CartInterface;
use Tipoff\Support\Contracts\Checkout\CartItemInterface;
use Tipoff\Support\Contracts\Models\UserInterface;
use Tipoff\Support\Contracts\Sellable\VoucherType as Sellable;
use Tipoff\Support\Models\BaseModel;
use Tipoff\Support\Traits\HasCreator;
use Tipoff\Support\Traits\HasPackageFactory;
use Tipoff\Support\Traits\HasUpdater;
use Tipoff\Vouchers\Exceptions\UnsupportedVoucherTypeException;
use Tipoff\Vouchers\Transformers\VoucherTypeTransformer;

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
class VoucherType extends BaseModel implements Sellable
{
    use HasPackageFactory;
    use HasCreator;
    use HasUpdater;
    use SoftDeletes;

    protected $casts = [
        'id' => 'integer',
        'is_sellable' => 'boolean',
        'sell_price' => 'integer',
        'amount' => 'integer',
        'participants' => 'integer',
        'expiration_days' => 'integer',
        'creator_id' => 'integer',
        'updater_id' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (VoucherType $vouchertype) {
            $vouchertype->expiration_days = $vouchertype->expiration_days ?: (config('vouchers.default_expiration_days') ?? 365);

            Assert::lazy()
                ->that(! empty($vouchertype->amount) && ! empty($vouchertype->participants), 'amount')->false('A voucher cannot have both an amount & number of participants.')
                ->verifyNow();
        });
    }

    //region RELATIONSHIPS

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    //endregion

    //region SCOPES

    public function scopeVisibleBy(Builder $query, UserInterface $user): Builder
    {
        return parent::scopeAlwaysVisible($query);
    }

    public function scopeIsSellable(Builder $query, bool $status = true): Builder
    {
        return $query->where('is_sellable', $status);
    }

    //endregion

    //region SELLABLE INTERFACE

    public function getTransformer($context = null)
    {
        return new VoucherTypeTransformer();
    }

    public function getViewComponent($context = null)
    {
        return implode('-', ['tipoff', 'voucher-type', $context]);
    }

    public function getDescription(): string
    {
        return $this->title;
    }

    public function createCartItem(int $locationId, int $quantity = 1): CartItemInterface
    {
        if (!$this->is_sellable) {
            throw new UnsupportedVoucherTypeException();
        }

        /** @var CartInterface $service */
        $service = findService(CartInterface::class);

       return $service::createItem($this, $this->slug, $this->sell_price, $quantity)
            ->setLocationId($locationId);
    }

    //endregion
}
