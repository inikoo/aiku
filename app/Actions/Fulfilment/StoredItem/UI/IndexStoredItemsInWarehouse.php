<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 22 Aug 2024 09:44:13 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\UI;

use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Actions\UI\Fulfilment\ShowFulfilmentDashboard;
use App\Enums\UI\Fulfilment\StoredItemsInWarehouseTabsEnum;
use App\Http\Resources\Fulfilment\ReturnStoredItemsResource;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Models\Fulfilment\StoredItem;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexStoredItemsInWarehouse extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;
    use WithFulfilmentCustomerSubNavigation;


    private Warehouse $parent;

    public function handle(Warehouse $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('slug', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(StoredItem::class);
        $query->leftJoin('pallet_stored_items', 'pallet_stored_items.stored_item_id', 'stored_items.id');
        $query->leftJoin('pallets', 'pallet_stored_items.pallet_id', 'pallets.id');
        $query->where('pallets.warehouse_id', $parent->id);

        return QueryBuilder::for(StoredItem::class)
            ->defaultSort('reference')
            ->allowedSorts(['slug', 'state', 'reference'])
            ->allowedFilters([$globalSearch, 'slug', 'state'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'FulfilmentCustomer' => [
                            'title'       => __("No stored items found"),
                            'count'       => $parent->count(),
                            'description' => __("No items stored in this customer")
                        ],
                        default => []
                    }
                )
                ->column(key: 'state', label: __('Delivery State'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'customer_name', label: __('Customer Name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'actions', label: __('Action'), canBeHidden: false, sortable: true, searchable: true)
                // ->column(key: 'notes', label: __('Notes'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('slug');
        };
    }


    public function jsonResponse(LengthAwarePaginator $storedItems): AnonymousResourceCollection
    {
        return StoredItemResource::collection($storedItems);
    }


    public function htmlResponse(LengthAwarePaginator $storedItems, ActionRequest $request): Response
    {
        $subNavigation = [];

        $icon       = ['fal', 'fa-narwhal'];
        $title      = __('stored items');
        $afterTitle = null;
        $iconRight  = null;



        return Inertia::render(
            'Org/Fulfilment/StoredItems',
            [
                'breadcrumbs'                                              => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'                                                    => __('stored items'),
                'pageHead'                                                 => [
                    'title'         => $title,
                    'afterTitle'    => $afterTitle,
                    'iconRight'     => $iconRight,
                    'icon'          => $icon,
                    'subNavigation' => $subNavigation,
                    'actions'       => [
                        'buttons' => [
                            'route' => [
                                'name'       => 'grp.org.hr.employees.create',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => __('stored items')
                        ]
                    ],
                ],
                'tabs'                                                     => [
                    'current'    => $this->tab,
                    'navigation' => StoredItemsInWarehouseTabsEnum::navigation(),
                ],
                StoredItemsInWarehouseTabsEnum::STORED_ITEMS->value => $this->tab == StoredItemsInWarehouseTabsEnum::STORED_ITEMS->value ?
                    fn () => StoredItemResource::collection($storedItems)
                    : Inertia::lazy(fn () => StoredItemResource::collection($storedItems)),

                StoredItemsInWarehouseTabsEnum::PALLET_STORED_ITEMS->value => $this->tab == StoredItemsInWarehouseTabsEnum::PALLET_STORED_ITEMS->value ?
                    fn () => ReturnStoredItemsResource::collection(IndexPalletStoredItems::run($this->parent))
                    : Inertia::lazy(fn () => ReturnStoredItemsResource::collection(IndexPalletStoredItems::run($this->parent))),

            ]
        )->table($this->tableStructure($storedItems, prefix: StoredItemsInWarehouseTabsEnum::STORED_ITEMS->value))
            ->table(
                IndexPalletStoredItems::make()->tableStructure(
                    $this->parent,
                    prefix: StoredItemsInWarehouseTabsEnum::PALLET_STORED_ITEMS->value
                )
            );
    }


    public function asController(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(StoredItemsInWarehouseTabsEnum::values());

        return $this->handle($warehouse);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            ShowFulfilmentDashboard::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.warehouses.show.inventory.stored_items.current.index',
                            'parameters' => [
                                'organisation' => $routeParameters['organisation'],
                                'warehouse'    => $routeParameters['warehouse'],
                            ]
                        ],
                        'label' => __('Stored items'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }


}
