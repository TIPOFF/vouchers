<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Tipoff\Support\Nova\BaseResource;
use Tipoff\Support\Nova\Fields\Enum;
use Tipoff\Vouchers\Enums\VoucherSource;

class Voucher extends BaseResource
{
    public static $model = \Tipoff\Vouchers\Models\Voucher::class;

    public static $title = 'code';

    public static $search = [
        'id',
    ];

    public static function indexQuery(NovaRequest $request, $query)
    {
        if ($request->user()->hasRole([
            'Admin',
            'Owner',
            'Accountant',
            'Executive',
            'Reservation Manager',
            'Reservationist',
        ])) {
            return $query;
        }

        return $query->whereHas('redemptionOrder', function ($orderlocation) use ($request) {
            return $orderlocation->whereIn('location_id', $request->user()->locations->pluck('id'));
        });
    }

    public static $group = 'Operations Units';

    /** @psalm-suppress UndefinedClass */
    protected array $filterClassList = [
        \Tipoff\Locations\Nova\Filters\OrderLocation::class,
    ];

    public function fieldsForIndex(NovaRequest $request)
    {
        return array_filter([
            ID::make()->sortable(),
            Text::make('Code')->sortable(),
            nova('user') ? BelongsTo::make('User', 'user', nova('user'))->sortable() : null,
            Enum::make('Source', 'source')->attach(VoucherSource::class),
            nova('voucher') ? BelongsTo::make('Voucher Type', 'voucher_type', nova('voucher'))->sortable() : null,
            nova('location') ? BelongsTo::make('Location', 'location', nova('location'))->sortable() : null,
            Date::make('Created At')->sortable(),
            nova('order') ? BelongsTo::make('Purchase Order', 'purchaseOrder', nova('order'))->sortable() : null,
            nova('order') ? BelongsTo::make('Redemption Order', 'redemptionOrder', nova('order'))->sortable() : null,
        ]);
    }

    public function fields(Request $request)
    {
        return array_filter([
            Text::make('Code')->exceptOnForms(),
            nova('user') ? BelongsTo::make('User', 'user', nova('user'))->searchable()->withSubtitles()->hideWhenUpdating() : null,
            Enum::make('Source', 'source')->attach(VoucherSource::class),
            nova('vouchertype') ? BelongsTo::make('Voucher Type', 'voucher_type', nova('vouchertype'))->hideWhenUpdating() : null,
            nova('location') ? BelongsTo::make('Location', 'location', nova('location'))->hideWhenUpdating() : null,
            nova('order') ? BelongsTo::make('Purchase Order', 'purchaseOrder', nova('order'))->exceptOnForms() : null,
            nova('order') ? BelongsTo::make('Redemption Order', 'redemptionOrder', nova('order'))->exceptOnForms() : null,
            Date::make('Redeemed At', 'redeemed_at')->format('DD-MM-YYYY HH:mm:ss')->exceptOnForms(),
            Currency::make('Amount')->asMinorUnits()
                ->step('0.01')
                ->resolveUsing(function ($value) {
                    return $value / 100;
                })
                ->fillUsing(function ($request, $model, $attribute) {
                    $model->$attribute = $request->$attribute * 100;
                })
                ->nullable(),
            Number::make('Participants')->nullable(),
            Date::make('Expires At', 'expires_at')->format('DD-MM-YYYY HH:mm:ss')->exceptOnForms(),

            new Panel('Data Fields', $this->dataFields()),
        ]);
    }

    protected function dataFields(): array
    {
        return array_merge(
            parent::dataFields(),
            $this->creatorDataFields(),
            $this->updaterDataFields(),
        );
    }
}
