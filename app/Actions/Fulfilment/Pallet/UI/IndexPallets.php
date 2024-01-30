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
use App\Enums\UI\FulfilmentTabsEnum;
use App\Http\Resources\Fulfilment\PalletResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
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
    private Organisation|FulfilmentCustomer|Location|Fulfilment|Warehouse $parent;

    public function handle(Organisation|FulfilmentCustomer|Location|Fulfilment|Warehouse $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('customer_reference', $value);
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
            default:
                $query->where('group_id', app('group')->id);
                break;
        }

        return $query->defaultSort('slug')
            ->allowedSorts(['customer_reference', 'slug'])
            ->allowedFilters([$globalSearch, $isNotLocated, 'customer_reference'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation|FulfilmentCustomer|Location|Fulfilment|Warehouse $parent, $prefix = null, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($prefix, $modelOperations) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title' => __("No pallets found"),
                        'count' => 0
                    ]
                )
                ->withModelOperations($modelOperations)
                ->column(key: 'customer_reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'customer_name', label: __('Customer Name'), canBeHidden: false, searchable: true)
                ->column(key: 'location', label: __('Location'), canBeHidden: false, searchable: true)
                ->column(key: 'state', label: __('State'), canBeHidden: false, searchable: true)
                ->column(key: 'status', label: __('Status'), canBeHidden: false, searchable: true)
                ->column(key: 'notes', label: __('Notes'), canBeHidden: false, searchable: true)
                ->defaultSort('slug');
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
        return PalletResource::collection($pallets);
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
                'data'        => PalletResource::collection($pallets),
            ]
        )->table($this->tableStructure($this->parent));
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($organisation, FulfilmentTabsEnum::PALLETS->value);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($warehouse);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inLocation(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, Location $location, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $location;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($location, FulfilmentTabsEnum::PALLETS->value);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {

        // dd($routeName);




        return match ($routeName) {
            'grp.org.warehouses.show.fulfilment.pallets.index'=>
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

            'xx'=>
            array_merge(
                ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.pallets.index',
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
