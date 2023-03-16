<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 15:30:55 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Inventory\Warehouse\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Models\Inventory\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @property array $breadcrumbs
 * @property string $title
 */
class IndexWarehouses extends InertiaAction
{
    use HasUIWarehouses;

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
            ->select(['code', 'warehouses.id', 'name', 'number_warehouse_areas', 'number_locations', 'slug'])
            ->leftJoin('warehouse_stats', 'warehouse_stats.warehouse_id', 'warehouses.id')
            ->allowedSorts(['code', 'name', 'number_warehouse_areas', 'number_locations'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        dd($this);
        $this->canEdit = $request->user()->can('inventory.warehouses.edit');
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


    public function jsonResponse(): AnonymousResourceCollection
    {
        return WarehouseResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $warehouses)
    {
        return Inertia::render(
            'Inventory/Warehouses',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('warehouses'),
                'pageHead'    => [
                    'title'   => __('warehouses'),
                    'create'  => $this->canEdit && $this->routeName=='inventory.warehouses.index' ? [
                        'route' => [
                            'name'       => 'inventory.warehouses.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('warehouse')
                    ] : false,
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
}
