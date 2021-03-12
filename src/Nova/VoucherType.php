<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Tipoff\Support\Nova\BaseResource;

class VoucherType extends BaseResource
{
    public static $model = \Tipoff\Vouchers\Models\VoucherType::class;

    public static $title = 'name';

    public static $search = [
        'id',
        'name',
        'title',
    ];

    public static $group = 'Z - Admin';

    /** @psalm-suppress UndefinedClass */
    protected array $filterClassList = [

    ];

    public function fieldsForIndex(NovaRequest $request)
    {
        return array_filter([
            ID::make(),
            Text::make('Name')->sortable(),
            Currency::make('Amount')->asMinorUnits()->sortable(),
            Number::make('Participants')->sortable(),
        ]);
    }

    public function fields(Request $request)
    {
        return array_filter([
            Text::make('Name (Internal)', 'name')->required(),
            Slug::make('Slug')->from('Name'),
            Text::make('Title (What Customers See)', 'title')->required(),
            Boolean::make('Is Sellable'),
            Currency::make('Sell Price')->asMinorUnits()
                ->step('0.01')
                ->resolveUsing(function ($value) {
                    return $value / 100;
                })
                ->fillUsing(function ($request, $model, $attribute) {
                    $model->$attribute = $request->$attribute * 100;
                })
                ->nullable(),
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
            Number::make('Expiration Days')->nullable(),

            nova('voucher') ? HasMany::make('Vouchers', 'vouchers', nova('voucher')) : null,

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
