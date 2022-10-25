<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 22:25:58 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\StockFamily;

use App\Actions\Inventory\ShowInventoryDashboard;
use App\Actions\UI\WithInertia;
use App\Http\Resources\Inventory\StockFamilyResource;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockFamily;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class IndexStockFamilies
{
    use AsAction;
    use WithInertia;


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
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('inventory.view')
            );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $request->validate();
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
                'title'       => __('families'),
                'pageHead'    => [
                    'title' => __('families'),
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


    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new ShowInventoryDashboard())->getBreadcrumbs(),
            [
                'inventory.stock-families.index' => [
                    'route' => 'inventory.stock-families.index',
                    'modelLabel' => [
                        'label' => __('families')
                    ],
                ],
            ]
        );
    }

}
