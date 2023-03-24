<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 13:21:43 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\StockFamily\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Inventory\StockFamilyResource;
use App\Models\Inventory\StockFamily;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexStockFamilies extends InertiaAction
{
    use HasUIStockFamilies;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('stock_families.code', 'LIKE', "$value%")
                    ->orWhere('stock_families.name', 'LIKE', "%$value%");
            });
        });


        return QueryBuilder::for(StockFamily::class)
            ->defaultSort('stock_families.code')
            ->select(['slug','code', 'stock_families.id as id', 'name', 'number_stocks'])
            ->leftJoin('stock_family_stats', 'stock_family_stats.stock_family_id', 'stock_families.id')
            ->allowedSorts(['code', 'name', 'number_stocks'])
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
                $request->user()->hasPermissionTo('inventory.stocks.view')
            );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        //$request->validate();
        $this->initialisation($request);
        return $this->handle();
    }


    public function jsonResponse(): AnonymousResourceCollection
    {
        return StockFamilyResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $stocks)
    {
        return Inertia::render(
            'Inventory/StockFamilies',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'pageHead'    => [
                    'title'   => __('families'),
                    'create'  => $this->canEdit && $this->routeName=='inventory.stock-families.index' ? [
                        'route' => [
                            'name'       => 'inventory.stock-families.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('family')
                    ] : false,
                ],
                'stockFamilies'  => StockFamilyResource::collection($stocks),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->column(key: 'code', label: 'code', canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_stocks', label: 'SKUs', canBeHidden: false, sortable: true)
                ->defaultSort('code');
        });
    }
}
