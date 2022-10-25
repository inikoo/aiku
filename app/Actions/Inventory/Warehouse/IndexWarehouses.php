<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 14 Sept 2022 19:25:53 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Warehouse;

use App\Actions\Inventory\ShowInventoryDashboard;
use App\Actions\UI\WithInertia;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Models\Inventory\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @property array $breadcrumbs
 * @property bool $canEdit
 * @property string $title
 */
class IndexWarehouses
{
    use AsAction;
    use WithInertia;


    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('warehouses.name', 'LIKE', "%$value%")
                    ->orWhere('warehouses.code', 'LIKE', "%$value%");
            });
        });


        return QueryBuilder::for(Warehouse::class)
            ->defaultSort('warehouses.code')
            ->select(['code', 'warehouses.id', 'name', 'number_warehouse_areas', 'number_locations'])
            ->leftJoin('warehouse_stats', 'warehouse_stats.warehouse_id', 'warehouses.id')
            ->allowedSorts(['code', 'name', 'number_warehouse_areas', 'number_locations'])
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
        return WarehouseResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $warehouses)
    {
        return Inertia::render(
            'Inventory/Warehouses.vue',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('warehouses'),
                'pageHead'    => [
                    'title' => __('warehouses'),
                ],
                'warehouses'  => WarehouseResource::collection($warehouses),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_warehouse_areas', label: __('warehouse areas'), canBeHidden: false, sortable: true)
                ->column(key: 'number_locations', label: __('locations'), canBeHidden: false, sortable: true)
                ->defaultSort('code');
        });
    }


    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new ShowInventoryDashboard())->getBreadcrumbs(),
            [
                'inventory.warehouses.index' => [
                    'route' => 'inventory.warehouses.index',
                    'modelLabel' => [
                        'label' => __('warehouses')
                    ],
                ],
            ]
        );
    }

}
