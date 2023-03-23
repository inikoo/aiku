<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 15:27:25 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Stock\UI;

use App\Actions\InertiaAction;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Inventory\StockResource;
use App\Models\Central\Tenant;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockFamily;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexStocks extends InertiaAction
{
    use HasUIStocks;


    public function handle(StockFamily|Tenant $parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('stocks.code', 'LIKE', "$value%")
                    ->orWhere('stocks.description', 'LIKE', "%$value%");
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::STOCK_MOVEMENTS->value);
        return QueryBuilder::for(Stock::class)
            ->defaultSort('stocks.code')
            ->select([
                'stock_families.slug as family_slug',
                'stock_families.code as family_code',
                'stocks.code',
                'stocks.slug',
                'stocks.description',
                'stock_value',
                'number_locations',
                'quantity'])
            ->leftJoin('stock_stats', 'stock_stats.stock_id', 'stocks.id')
            ->leftJoin('stock_families', 'stock_families.id', 'stocks.stock_family_id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'StockFamily') {
                    $query->where('stocks.stock_family_id', $parent->id);
                }
            })
            ->allowedSorts(['code', 'family_code','description', 'number_locations', 'number_locations','quantity'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::STOCK_MOVEMENTS->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent): Closure
    {
        return function (InertiaTable $table) use ($parent) {
            $table
                ->name(TabsAbbreviationEnum::STOCK_MOVEMENTS->value)
                ->pageName(TabsAbbreviationEnum::STOCK_MOVEMENTS->value.'Page');

            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true);


            $table->column(key: 'family_code', label: __('family'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('inventory.stocks.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('inventory.view')
            );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->routeName = $request->route()->getName();
        return $this->handle(app('currentTenant'));
    }

    public function inStockFamily(StockFamily $stockFamily, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($stockFamily);
    }

    public function jsonResponse(LengthAwarePaginator $stocks): AnonymousResourceCollection
    {
        return StockResource::collection($stocks);
    }


    public function htmlResponse(LengthAwarePaginator $stocks, ActionRequest $request)
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());

        return Inertia::render(
            'Inventory/Stocks',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('stocks'),
                'pageHead'    => [
                    'title'   => __('stocks'),
                    'create'  => $this->canEdit && $this->routeName=='inventory.stocks.index' ? [
                        'route' => [
                            'name'       => 'inventory.stocks.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('stock')
                    ] : false,
                ],
                'data'  => StockResource::collection($stocks),


            ]
        )->table($this->tableStructure($parent));
    }
}
