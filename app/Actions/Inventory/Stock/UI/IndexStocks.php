<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Wed, 15 Mar 2023 15:27:25 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Stock\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Http\Resources\Inventory\StockResource;
use App\Models\Central\Tenant;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockFamily;
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
    private StockFamily|Tenant $parent;


    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('stocks.code', 'LIKE', "$value%")
                    ->orWhere('stocks.description', 'LIKE', "%$value%");
            });
        });


        return QueryBuilder::for(Stock::class)
            ->defaultSort('stocks.code')
            ->select(['code', 'stocks.id as id', 'description', 'stock_value', 'number_locations','quantity'])
            ->leftJoin('stock_stats', 'stock_stats.stock_id', 'stocks.id')
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'StockFamily') {
                    $query->where('stocks.stock_family_id', $this->parent->id);
                }
            })
            ->allowedSorts(['code', 'description', 'number_locations', 'number_locations','quantity'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
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
        //$request->validate();
        $this->initialisation($request);
        return $this->handle();
    }

    public function inStockFamily(StockFamily $stockFamily, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $stockFamily;
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle();
    }

    public function jsonResponse(): AnonymousResourceCollection
    {
        return StockResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $stocks)
    {
        return Inertia::render(
            'Inventory/Stocks',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('stocks'),
                'pageHead'    => [
                    'title' => __('stocks'),
                    'create'  => $this->canEdit && $this->routeName=='inventory.stocks.index' ? [
                        'route' => [
                            'name'       => 'inventory.stocks.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('stock')
                    ] : false,
                ],
                'stocks'  => StockResource::collection($stocks),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->column(key: 'code', label: 'SKU', canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'description', label: __('description'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'quantity', label: __('stock'), canBeHidden: false, sortable: true)
                ->defaultSort('code');
        });
    }



}
