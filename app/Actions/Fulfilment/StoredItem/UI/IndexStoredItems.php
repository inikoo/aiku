<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\UI;

use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\StoredItemAudit\UI\IndexStoredItemAudits;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Overview\ShowGroupOverviewHub;
use App\Enums\UI\Fulfilment\StoredItemsInWarehouseTabsEnum;
use App\Http\Resources\Fulfilment\ReturnStoredItemsResource;
use App\Http\Resources\Fulfilment\StoredItemAuditsResource;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItem;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexStoredItems extends OrgAction
{
    use WithFulfilmentCustomerSubNavigation;

    private Group|Organisation|FulfilmentCustomer|Fulfilment $parent;

    public function handle(Group|Organisation|FulfilmentCustomer|Fulfilment $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('slug', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        return QueryBuilder::for(StoredItem::class)
            ->defaultSort('slug')
            ->when($parent, function ($query) use ($parent) {
                if ($parent instanceof FulfilmentCustomer) {
                    $query->where('fulfilment_customer_id', $parent->id);
                }

                if ($parent instanceof Fulfilment) {
                    $query->where('fulfilment_id', $parent->id);
                }

                if ($parent instanceof Group) {
                    $query->where('stored_items.group_id', $parent->id);
                }
            })
            ->allowedSorts(['slug', 'state'])
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

            // dd(class_basename($parent) );
            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'FulfilmentCustomer' => [
                            'title'         => __("No stored items found"),
                            'count'         => $parent->count(),
                            'description'   => __("No items stored in this customer")
                        ],
                        'Group' => [
                            'title'         => __("No stored items found"),
                            'count'         => $parent->count(),
                            'description'   => __("No items stored in this group")
                        ],
                        default => []
                    }
                )
                ->column(key: 'state', label: __('Delivery State'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'customer_name', label: __('Customer Name'), canBeHidden: false, sortable: true, searchable: true);
            if (class_basename($parent) == 'Group') {
                $table->column(key: 'organisation_name', label: __('Organisation'), canBeHidden: false, sortable: true, searchable: true);
            }
            $table->column(key: 'customer_name', label: __('Customer Name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'actions', label: __('Action'), canBeHidden: false, sortable: true, searchable: true)
                // ->column(key: 'notes', label: __('Notes'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('slug');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Group) {
            return $request->user()->hasPermissionTo("group-overview");
        }
        $this->canEdit = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.view")
            );
    }


    public function jsonResponse(LengthAwarePaginator $storedItems): AnonymousResourceCollection
    {
        return StoredItemResource::collection($storedItems);
    }


    public function htmlResponse(LengthAwarePaginator $storedItems, ActionRequest $request): Response
    {
        // dd($this->parent);
        // dd(StoredItemAuditsResource::collection(IndexStoredItemAudits::run($this->parent)));
        $subNavigation = [];

        $icon      = ['fal', 'fa-narwhal'];
        $title     = __("customer's sKUs");
        $afterTitle = null;
        $iconRight = null;

        if ($this->parent instanceof  FulfilmentCustomer) {
            $subNavigation = $this->getFulfilmentCustomerSubNavigation($this->parent, $request);
            $icon         = ['fal', 'fa-user'];
            $title        = $this->parent->customer->name;
            $iconRight    = [
                'icon' => 'fal fa-narwhal',
            ];
            $afterTitle = [

                'label'     => __("customer's sKUs")
            ];
        }
        return Inertia::render(
            'Org/Fulfilment/StoredItems',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters(),
                ),
                'title'       => __("customer's sKUs"),
                'pageHead'    => [
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
                            'label' => __("customer's sKUs")
                        ]
                    ],
                ],
                'tabs'                                              => [
                    'current'    => $this->tab,
                    'navigation' => StoredItemsInWarehouseTabsEnum::navigation(),
                ],
                StoredItemsInWarehouseTabsEnum::STORED_ITEMS->value => $this->tab == StoredItemsInWarehouseTabsEnum::STORED_ITEMS->value ?
                fn () => StoredItemResource::collection($storedItems)
                : Inertia::lazy(fn () => StoredItemResource::collection($storedItems)),

                StoredItemsInWarehouseTabsEnum::PALLET_STORED_ITEMS->value => $this->tab == StoredItemsInWarehouseTabsEnum::PALLET_STORED_ITEMS->value ?
                    fn () => ReturnStoredItemsResource::collection(IndexPalletStoredItems::run($this->parent))
                    : Inertia::lazy(fn () => ReturnStoredItemsResource::collection(IndexPalletStoredItems::run($this->parent))),

                StoredItemsInWarehouseTabsEnum::STORED_ITEM_AUDITS->value => $this->tab == StoredItemsInWarehouseTabsEnum::STORED_ITEM_AUDITS->value ?
                    fn () => StoredItemAuditsResource::collection(IndexStoredItemAudits::run($this->parent))
                    : Inertia::lazy(fn () => StoredItemAuditsResource::collection(IndexStoredItemAudits::run($this->parent))),

            ]
        )->table($this->tableStructure($this->parent, prefix: StoredItemsInWarehouseTabsEnum::STORED_ITEMS->value))
        ->table(IndexStoredItemAudits::make()->tableStructure(parent: $this->parent, prefix: StoredItemsInWarehouseTabsEnum::STORED_ITEM_AUDITS->value))
        ->table(
            IndexPalletStoredItems::make()->tableStructure(
                $this->parent,
                prefix: StoredItemsInWarehouseTabsEnum::PALLET_STORED_ITEMS->value
            )
        );
    }

    public function inGroup(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = group();
        $this->initialisationFromGroup($this->parent, $request)->withTab(StoredItemsInWarehouseTabsEnum::values());

        return $this->handle($this->parent, 'stored_items');
    }


    public function asController(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request);
        return $this->handle($fulfilmentCustomer, 'stored_items');
    }



    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(StoredItemsInWarehouseTabsEnum::values());

        return $this->handle($fulfilmentCustomer, 'stored_items');
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        // dd($routeParameters);
        $headCrumb = function (array $routeParameters) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __("customer's sKUs"),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ];
        };

        return match ($routeName) {
            'grp.overview.fulfilment.stored-items.index' =>
            array_merge(
                ShowGroupOverviewHub::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.show.stored-items.index',
                                'parameters' => $routeParameters
                            ],
                            'label' => __("customer's sKUs"),
                            'icon'  => 'fal fa-bars',
                        ],

                    ]
                ]
            ),
        };
    }
}
