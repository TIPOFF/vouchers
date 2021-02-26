<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Models;

use Assert\Assert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Tipoff\Checkout\Models\Cart;
use Tipoff\Checkout\Models\Order;
use Tipoff\Support\Contracts\Checkout\CartInterface;
use Tipoff\Support\Contracts\Checkout\CodedCartAdjustment;
use Tipoff\Support\Contracts\Checkout\Vouchers\VoucherInterface;
use Tipoff\Support\Models\BaseModel;
use Tipoff\Support\Traits\HasCreator;
use Tipoff\Support\Traits\HasPackageFactory;
use Tipoff\Support\Traits\HasUpdater;
use Tipoff\Vouchers\Exceptions\UnsupportedVoucherTypeException;
use Tipoff\Vouchers\Exceptions\VoucherRedeemedException;
use Tipoff\Vouchers\Services\Voucher\CalculateAdjustments;

/**
 * @property int id
 * @property string code
 * @property int amount
 * @property int participants
 * @property VoucherType voucher_type
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
class Voucher extends BaseModel implements VoucherInterface
{
    use HasPackageFactory;
    use HasCreator;
    use HasUpdater;
    use SoftDeletes;

    /** Voucher type used in partial redemptions. */
    const PARTIAL_REDEMPTION_VOUCHER_TYPE_ID = 7;

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

        static::saving(function (Voucher $voucher) {
            $voucher->expires_at = $voucher->expires_at ?: Carbon::parse($voucher->created_at)->addDays($voucher->voucher_type->expiration_days);
            $voucher->redeemable_at = $voucher->redeemable_at ?: Carbon::now()->addHours(self::DEFAULT_REDEEMABLE_HOURS);
            $voucher->code = strtoupper($voucher->code ?: static::generateVoucherCode());

            Assert::lazy()
                ->that(empty($voucher->amount) && empty($voucher->participants), 'amount')->false('A voucher must have either an amount or number of participants.')
                ->that(! empty($voucher->amount) && ! empty($voucher->participants), 'amount')->false('A voucher cannot have both an amount & number of participants.')
                ->verifyNow();
        });
    }

    public function generateCode(): self
    {
        $this->code = static::generateVoucherCode();

        return $this;
    }

    public function scopeByUser(Builder $query, $user): Builder
    {
        return $query->whereHas('customer', function ($q) use ($user) {
            $q->where('user_id', $user->id ?? 0);
        });
    }

    public function scopeValidAt(Builder $query, $date): Builder
    {
        return $query
            ->whereDate('expires_at', '>=', $date)
            ->whereDate('redeemable_at', '<=', $date)
            ->whereNull('redeemed_at');
    }

    public function scopeByCartId(Builder $query, int $cartId): Builder
    {
        return $query->whereHas('carts', function ($q) use ($cartId) {
            $q->where('id', $cartId);
        });
    }

    public function scopeByOrderId(Builder $query, int $orderId): Builder
    {
        return $query->where('order_id', $orderId);
    }

    public function reset(): self
    {
        $this->redeemed_at = null;
        $this->purchase_order_id = null;
        $this->order_id = null;

        $this->carts()->sync([]);

        $this->save();

        return $this;
    }

    public function isOwner($userId): bool
    {
        return $this->getUser()->id === $userId;
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

    public function decoratedAmount(): string
    {
        return '$' . number_format($this->amount / 100, 2, '.', ',');
    }

    public function redeem(?Order $order = null): self
    {
        $this->redeemed_at = Carbon::now();
        if ($order) {
            $this->order()->associate($order);
        }

        return $this;
    }

    public function getUser()
    {
        // TODO - change to model interface -
        // $customer = findModel(CustomerInterface::class, $this->customer_id);
        // return $customer ? $customer->getUser() : null;
        $customer = $this->customer;

        return $customer ? $customer->user : null;
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
        return $this->belongsToMany(Cart::class)->withTimestamps();
    }

    public static function markCartDeductionsAsUsed(CartInterface $cart): void
    {
        $vouchers = Voucher::query()->byCartId($cart->getId())->get();

        $vouchers->each(function (Voucher $voucher) {
            $voucher->redeem()->save();
        });
    }

    public static function generateVoucherCode(): string
    {
        do {
            $code = Carbon::now('America/New_York')->format('ymd').Str::upper(Str::random(3));
        } while (Voucher::where('code', $code)->first());

        return $code;
    }

    public static function issuePartialRedemptionVoucher(CartInterface $cart, int $locationId, int $amount, int $userId): VoucherInterface
    {
        $vouchers = Voucher::query()->byCartId($cart->getId())->get();

        return Voucher::create([
            'code' => self::generateVoucherCode(),
            'location_id' => $locationId,
            'customer_id' => $userId,
            'voucher_type_id' => self::PARTIAL_REDEMPTION_VOUCHER_TYPE_ID,
            'redeemable_at' => now(),
            'amount' => $amount,
            'expires_at' => $vouchers->first()->expires_at,
            'creator_id' => $userId,
            'updater_id' => $userId,
        ]);
    }

    //region INTERFACE

    public static function findByCode(string $code): ?CodedCartAdjustment
    {
        return Voucher::query()->where('code', $code)->validAt(Carbon::now())->first();
    }

    public static function calculateAdjustments(CartInterface $cart): void
    {
        app(CalculateAdjustments::class)($cart);
    }

    public static function getCodesForCart(CartInterface $cart): array
    {
        return Voucher::query()->byCartId($cart->getId())->pluck('code')->toArray();
    }

    public function applyToCart(CartInterface $cart)
    {
        if ($this->participants > 0) {
            throw new UnsupportedVoucherTypeException('Participants vouchers not supported yet.');
        }

        if (! empty($this->redeemed_at)) {
            throw new VoucherRedeemedException();
        }

        if ($this->amount > 0) {
            $this->carts()->syncWithoutDetaching([$cart->getId()]);
        }

        return $this;
    }

    public function removeFromCart(CartInterface $cart)
    {
        $this->carts()->detach($this->id);

        return $this;
    }

    //endregion
}
