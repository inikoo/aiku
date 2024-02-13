<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Jan 2024 20:05:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStatusEnum;
use App\Http\Resources\Fulfilment\PalletDeliveriesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

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
                ->orWhereStartWith('customer_reference', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(PalletDelivery::class);

        $queryBuilder->where('status', PalletDeliveryStatusEnum::OUT);

        if($parent instanceof Fulfilment) {
            $queryBuilder->where('pallet_deliveries.fulfilment_id', $parent->id);
        } elseif($parent instanceof Warehouse) {
            $queryBuilder->where('pallet_deliveries.warehouse_id', $parent->id);
        } else {
            $queryBuilder->where('pallet_deliveries.fulfilment_customer_id', $parent->id);
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
                    $parent instanceof Fulfilment ? [
                        'title'       => __("You don't have any customer yet").' 😭',
                        'description' => __("Dont worry soon you will be pretty busy"),
                        'count'       => $parent->shop->crmStats->number_customers,
                        'action'      => [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new customer'),
                            'label'   => __('customer'),
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.customers.create',
                                'parameters' => [$parent->organisation->slug, $parent->slug]
                            ]
                        ]
                    ] : null
                )
                ->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon')
                ->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'customer reference', label: __('customer reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'pallets', label: __('pallets'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $customers): AnonymousResourceCollection
    {
        return PalletDeliveriesResource::collection($customers);
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
                'title'       => __('pallet deliveries'),
                'pageHead'    => [
                    'title'     => __('deliveries'),
                    'container' => $container,
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-truck-couch'],
                        'title' => __('delivery')
                    ]
                ],
                'data'        => PalletDeliveriesResource::collection($customers),

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
                        'label' => __('deliveries'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };


        return match ($routeName) {

            'grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.index'=> array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs(
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.index',
                        'parameters' => Arr::only($routeParameters, ['organisation','fulfilment','fulfilmentCustomer'])
                    ]
                )
            ),
            'grp.org.fulfilments.show.operations.pallet-deliveries.index' => array_merge(
                ShowFulfilment::make()->getBreadcrumbs(
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.fulfilments.show.operations.pallet-deliveries.index',
                        'parameters' => Arr::only($routeParameters, ['organisation','fulfilment'])
                    ]
                )
            ),
            'grp.org.warehouses.show.fulfilment.pallet-deliveries.index' => array_merge(
                ShowWarehouse::make()->getBreadcrumbs(
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.warehouses.show.fulfilment.pallet-deliveries.index',
                        'parameters' => Arr::only($routeParameters, ['organisation','warehouse'])
                    ]
                )
            ),
        };



    }
}
