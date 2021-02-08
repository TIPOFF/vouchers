<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Tipoff\Support\Nova\Resource;

class Voucher extends Resource
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

        return $query->whereHas('order', function ($orderlocation) use ($request) {
            return $orderlocation->whereIn('location_id', $request->user()->locations->pluck('id'));
        });
    }

    public static $group = 'Operations Units';

    public function fieldsForIndex(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Code')->sortable(),
            BelongsTo::make('Customer', 'customer', config('vouchers.nova_class.customer'))->sortable(),
            BelongsTo::make('Voucher Type', 'voucher_type', VoucherType::class)->sortable(),
            BelongsTo::make('Location', 'location', config('vouchers.nova_class.location'))->sortable(),
            Date::make('Created At')->sortable(),
            BelongsTo::make('Purchase Order', 'purchaseOrder', config('vouchers.nova_class.order'))->sortable(),
            BelongsTo::make('Redemption Order', 'redemptionOrder', config('vouchers.nova_class.order'))->sortable(),
        ];
    }

    public function fields(Request $request)
    {
        return [
            Text::make('Code')->exceptOnForms(),
            BelongsTo::make('Customer', 'customer', config('vouchers.nova_class.customer'))->searchable()->withSubtitles()->hideWhenUpdating(),
            BelongsTo::make('Voucher Type', 'voucher_type', VoucherType::class)->hideWhenUpdating(),
            BelongsTo::make('Location', 'location', config('vouchers.nova_class.location'))->hideWhenUpdating(),
            BelongsTo::make('Purchase Order', 'purchaseOrder', config('vouchers.nova_class.order'))->exceptOnForms(),
            BelongsTo::make('Redemption Order', 'redemptionOrder', config('vouchers.nova_class.order'))->exceptOnForms(),
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
        ];
    }

    protected function dataFields()
    {
        return [
            ID::make(),
            BelongsTo::make('Created By', 'creator', config('vouchers.nova_class.user'))->exceptOnForms(),
            DateTime::make('Created At')->exceptOnForms(),
            BelongsTo::make('Updated By', 'updater', config('vouchers.nova_class.user'))->exceptOnForms(),
            DateTime::make('Updated At')->exceptOnForms(),
        ];
    }

    public function cards(Request $request)
    {
        return [];
    }

    public function filters(Request $request)
    {
        return [
            // TODO -- class will need app level override to add this for now...
            // new Filters\OrderLocation,
        ];
    }

    public function lenses(Request $request)
    {
        return [];
    }

    public function actions(Request $request)
    {
        return [];
    }
}
