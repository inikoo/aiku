<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 12 Mar 2024 14:15:37 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItemReturn\UI;

use App\Actions\OrgAction;
use App\Actions\UI\Retina\Storage\ShowStorageDashboard;
use App\Http\Resources\Fulfilment\PalletReturnsResource;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItemReturn;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexStoredItemReturns extends OrgAction
{
    private Fulfilment|Warehouse|FulfilmentCustomer $parent;

    public function authorize(ActionRequest $request): bool
    {
        if($this->parent instanceof Fulfilment or $this->parent instanceof FulfilmentCustomer) {
            $this->canEdit = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");

        } elseif($this->parent instanceof Warehouse) {
            $this->canEdit = $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.edit");
            return $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.view");
        }

        return false;
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilmentCustomer);
    }

    public function handle(FulfilmentCustomer|Fulfilment|Warehouse $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('reference', $value)
                    ->orWhereStartWith('customer_reference', $value)
                    ->orWhereStartWith('slug', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(StoredItemReturn::class);

        if($parent instanceof Fulfilment) {
            $queryBuilder->whereHas('fulfilmentCustomer', function ($query) use ($parent) {
                $query->where('fulfilment_id', $parent->id);
            });
        } elseif($parent instanceof Warehouse) {
            $queryBuilder->where('warehouse_id', $parent->id);
        } elseif($parent instanceof FulfilmentCustomer) {
            $queryBuilder->where('fulfilment_customer_id', $parent->id);
        }

        return $queryBuilder
            ->defaultSort('reference')
            ->allowedSorts(['reference'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Fulfilment|Warehouse|FulfilmentCustomer $parent, ?array $modelOperations = null, $prefix = null): \Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix . 'Page');
            }


            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Fulfilment' => [
                            'title'       => __('No stored item returns found for this shop'),
                            'count'       => $parent->stats->number_stored_items_status_returned
                        ],
                        'Warehouse' => [
                            'title'       => __('No stored item returns found for this warehouse'),
                            'description' => __('This warehouse has not received any stored  item returns yet'),
                            'count'       => $parent->stats->number_stored_items_status_returned
                        ],
                        'FulfilmentCustomer' => [
                            'title'       => __('No stored item returns found for this customer'),
                            'description' => __('This customer has not received any stored  item returns yet'),
                            'count'       => $parent->number_stored_items_status_returned,
                            'action'      => [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('Return new stored item return'),
                                'label'   => __('Stored Item Return'),
                                'route'   => [
                                    'method'     => 'post',
                                    'name'       => 'grp.models.fulfilment-customer.stored-item-return.store',
                                    'parameters' => [$parent->id]
                                ]
                            ]
                        ]
                    }
                )
                ->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon')
                ->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'customer reference', label: __('customer reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'items', label: __('items'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $storedItems): AnonymousResourceCollection
    {
        return StoredItemResource::collection($storedItems);
    }

    public function htmlResponse(LengthAwarePaginator $customers, ActionRequest $request): Response
    {
        $container = [
            'icon'    => ['fal', 'fa-stored item-alt'],
            'tooltip' => __('Customer Fulfilment'),
            'label'   => Str::possessive($this->parent->customer->name)
        ];

        return Inertia::render(
            'Org/Fulfilment/StoredItemReturns',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'    => __('stored item returns'),
                'pageHead' => [
                    'title' => __('stored item returns'),
                    // 'container' => $container,
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-truck-couch'],
                        'title' => __('return')
                    ],
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('New Return'),
                            'route' => [
                                'method'     => 'post',
                                'name'       => 'retina.models.stored-item-returns.store',
                                'parameters' => []
                            ]
                        ]
                    ]
                ],
                'data' => PalletReturnsResource::collection($customers),

            ]
        )->table($this->tableStructure($this->parent, prefix: 'stored_item_returns'));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('stored item returns'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {

            'retina.storage.stored-item-returns.index' => array_merge(
                ShowStorageDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'       => 'retina.storage.stored-item-returns.index',
                        'parameters' => []
                    ]
                )
            ),
        };
    }
}
