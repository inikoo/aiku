<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 14 Feb 2024 16:17:44 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\OrgAction;
use App\Http\Resources\Fulfilment\PalletReturnsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexPalletReturns extends OrgAction
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

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($warehouse);
    }

    public function handle(Fulfilment|Warehouse|FulfilmentCustomer $parent, $prefix = null): LengthAwarePaginator
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

        $queryBuilder = QueryBuilder::for(PalletReturn::class);

        if($parent instanceof Fulfilment) {
            $queryBuilder->where('pallet_returns.fulfilment_id', $parent->id);
        } elseif($parent instanceof Warehouse) {
            $queryBuilder->where('pallet_returns.warehouse_id', $parent->id);
        } else {
            $queryBuilder->where('pallet_returns.fulfilment_customer_id', $parent->id);
        }

        return $queryBuilder
            ->defaultSort('reference')
            ->allowedSorts(['reference'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Fulfilment|Warehouse|FulfilmentCustomer $parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }


            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Fulfilment' => [
                            'title'       => __('No pallet returns found for this shop'),
                            'count'       => $parent->stats->number_pallet_returns
                        ],
                        'Warehouse' => [
                            'title'       => __('No pallet returns found for this warehouse'),
                            'description' => __('This warehouse has not received any pallet returns yet'),
                            'count'       => $parent->stats->number_pallet_returns
                        ],
                        'FulfilmentCustomer' => [
                            'title'       => __('No pallet returns found for this customer'),
                            'description' => __('This customer has not received any pallet returns yet'),
                            'count'       => $parent->number_pallet_returns,
                            'action'      => [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('Create new pallet return'),
                                'label'   => __('Pallet return'),
                                'route'   => [
                                    'method'     => 'post',
                                    'name'       => 'grp.models.fulfilment-customer.pallet-return.store',
                                    'parameters' => [$parent->id]
                                ]
                            ]
                        ]
                    }
                )
                ->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon')
                ->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'customer reference', label: __('customer reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'pallets', label: __('pallets'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $customers): AnonymousResourceCollection
    {
        return PalletReturnsResource::collection($customers);
    }

    public function htmlResponse(LengthAwarePaginator $customers, ActionRequest $request): Response
    {
        $container = null;
        if($this->parent instanceof Fulfilment) {
            $container = [
                'icon'    => ['fal', 'fa-pallet-alt'],
                'tooltip' => __('Fulfilment Shop'),
                'label'   => Str::possessive($this->fulfilment->shop->name)

            ];
        } elseif($this->parent instanceof Warehouse) {
            $container = [
                'icon'    => ['fal', 'fa-warehouse-alt'],
                'tooltip' => __('Warehouse'),
                'label'   => Str::possessive($this->warehouse->name)

            ];
        }

        return Inertia::render(
            'Org/Fulfilment/PalletDeliveries',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('pallet returns'),
                'pageHead'    => [
                    'title'     => __('returns'),
                    'container' => $container,
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-sign-out'],
                        'title' => __('returns')
                    ],
                    'actions' => [
                        [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('Create new pallet return'),
                            'label'   => __('Pallet return'),
                            'route'   => [
                                'method'     => 'post',
                                'name'       => 'grp.models.fulfilment-customer.pallet-return.store',
                                'parameters' => [$this->parent->id]
                            ]
                        ]
                    ]
                ],
                'data'        => PalletReturnsResource::collection($customers),

            ]
        )->table($this->tableStructure($this->parent));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {

        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('returns'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {

            'grp.org.warehouses.show.fulfilment.pallet-returns.index'=> array_merge(
                ShowWarehouse::make()->getBreadcrumbs(
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.warehouses.show.fulfilment.pallet-returns.index',
                        'parameters' => Arr::only($routeParameters, ['organisation','warehouse'])
                    ]
                )
            ),

            'grp.org.fulfilments.show.crm.customers.show.pallet-returns.index'=> array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs(
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet-returns.index',
                        'parameters' => Arr::only($routeParameters, ['organisation','fulfilment','fulfilmentCustomer'])
                    ]
                )
            ),
            'grp.org.fulfilments.show.operations.pallet-returns.index' => array_merge(
                ShowFulfilment::make()->getBreadcrumbs(
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.fulfilments.show.operations.pallet-returns.index',
                        'parameters' => Arr::only($routeParameters, ['organisation','fulfilment'])
                    ]
                )
            ),

        };



    }
}
