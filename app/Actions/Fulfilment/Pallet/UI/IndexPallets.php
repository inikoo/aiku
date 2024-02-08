<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 18:40:36 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Inventory\Location;
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

class IndexPallets extends OrgAction
{
    private Organisation|FulfilmentCustomer|Location|Fulfilment|Warehouse|PalletDelivery $parent;

    protected function getElementGroups(): array
    {
        return [
            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    PalletStateEnum::labels(),
                    PalletStateEnum::count($this->organisation)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('state', $elements);
                }

            ],

        ];
    }

    public function handle(Organisation|FulfilmentCustomer|Location|Fulfilment|Warehouse|PalletDelivery $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('customer_reference', $value)
                ->orWhereStartWith('reference', $value);
            });
        });

        $isNotLocated = AllowedFilter::callback('located', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                if ($value) {
                    $query->whereNotNull('location_id');
                }
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(Pallet::class);

        switch (class_basename($parent)) {
            case "FulfilmentCustomer":
                $query->where('fulfilment_customer_id', $parent->id);
                break;
            case "Location":
                $query->where('location_id', $parent->id);
                break;
            case "Organisation":
                $query->where('organisation_id', $parent->id);
                break;
            case "Fulfilment":
                $query->where('fulfilment_id', $parent->id);
                break;
            case "Warehouse":
                $query->where('warehouse_id', $parent->id);
                break;
            case "PalletDelivery":
                $query->where('pallet_delivery_id', $parent->id);
                break;
            default:
                $query->where('group_id', app('group')->id);
                break;
        }


        if(!$parent instanceof PalletDelivery) {
            $query->where('state', '!=', PalletStateEnum::IN_PROCESS);
        }



        return $query->defaultSort('reference')
            ->allowedSorts(['customer_reference', 'reference'])
            ->allowedFilters([$globalSearch, $isNotLocated, 'customer_reference'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation|FulfilmentCustomer|Location|Fulfilment|Warehouse|PalletDelivery $parent, $prefix = null, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($prefix, $modelOperations, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }


            $emptyStateData = [
                'icons' => ['fal fa-pallet'],
                'title' => '',
                'count' => 0
            ];


            if ($parent instanceof Fulfilment) {
                $emptyStateData['title'] = __("There is not pallets in this fulfilment shop");
            }
            if ($parent instanceof Warehouse) {
                $emptyStateData['title'] = __("There isn't any fulfilment pallet in this warehouse");
            }
            if ($parent instanceof FulfilmentCustomer) {
                $emptyStateData['title'] = __("This customer don't have any pallets");
            }

            $table->withGlobalSearch()
                ->withEmptyState($emptyStateData)
                ->withModelOperations($modelOperations);


            $table->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon');
            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);


            $table->column(key: 'customer_reference', label: __('customer reference'), canBeHidden: false, sortable: true, searchable: true);


            if ($parent instanceof Organisation || $parent instanceof Fulfilment || $parent instanceof Warehouse) {
                $table->column(key: 'customer_name', label: __('Customer'), canBeHidden: false, searchable: true);
            }

            if ($parent instanceof Organisation || $parent instanceof Fulfilment || $parent instanceof Warehouse) {
                $table->column(key: 'location', label: __('Location'), canBeHidden: false, searchable: true);
            }


            $table->column(key: 'notes', label: __('Notes'), canBeHidden: false, searchable: true)
                ->column(key: 'actions', label: ' ', canBeHidden: false, searchable: true)
                ->defaultSort('reference');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Warehouse || $this->parent instanceof Location) {
            $this->canEdit = $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.edit");

            return $request->user()->hasPermissionTo("fulfilment.{$this->warehouse->id}.view");
        } elseif ($this->parent instanceof Fulfilment) {
            $this->canEdit = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.stored-items.edit");

            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.stored-items.view");
        }

        return false;
    }


    public function jsonResponse(LengthAwarePaginator $pallets): AnonymousResourceCollection
    {
        return PalletsResource::collection($pallets);
    }


    public function htmlResponse(LengthAwarePaginator $pallets, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Fulfilment/Pallets',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('pallets'),
                'pageHead'    => [
                    'title' => __('pallets')
                ],
                'data'        => PalletsResource::collection($pallets),
            ]
        )->table($this->tableStructure($this->parent, 'pallets'));
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($organisation, 'pallets');
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($warehouse, 'pallets');
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inLocation(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, Location $location, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $location;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($location);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return match ($routeName) {
            'grp.org.warehouses.show.fulfilment.pallets.index' =>
            array_merge(
                ShowWarehouse::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.warehouses.show.fulfilment.pallets.index',
                                'parameters' => [
                                    'organisation' => $routeParameters['organisation'],
                                    'warehouse'    => $routeParameters['warehouse'],
                                ]
                            ],
                            'label' => __('pallets'),
                            'icon'  => 'fal fa-bars',
                        ],

                    ]
                ]
            ),

            'grp.org.fulfilments.show.operations.pallets.index' =>
            array_merge(
                ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.operations.pallets.index',
                                'parameters' => [
                                    'organisation' => $routeParameters['organisation'],
                                    'fulfilment'   => $routeParameters['fulfilment'],
                                ]
                            ],
                            'label' => __('pallets'),
                            'icon'  => 'fal fa-bars',
                        ],

                    ]
                ]
            )
        };
    }
}
