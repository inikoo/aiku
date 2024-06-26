<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 18:40:36 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexPallets extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;
    use WithFulfilmentCustomerSubNavigation;

    private FulfilmentCustomer|Fulfilment|Location $parent;

    private bool $selectStoredPallets = false;

    protected function getElementGroups(FulfilmentCustomer|Fulfilment|Location $parent): array
    {
        return [
            'status' => [
                'label'    => __('Status'),
                'elements' => array_merge_recursive(
                    PalletStatusEnum::labels($parent),
                    PalletStatusEnum::count($parent)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('pallets.status', $elements);
                }
            ],


        ];
    }

    public function handle(FulfilmentCustomer|Fulfilment|Location $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('pallets.customer_reference', $value)
                    ->orWhereWith('pallets.reference', $value);
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
            case "Fulfilment":
                $query->where('pallets.fulfilment_id', $parent->id);
                break;
            case "PalletDelivery":
                $query->where('pallets.pallet_delivery_id', $parent->id);
                break;
            case "Location":
                $query->where('pallets.location_id', $parent->id);
                break;
            default:
                abort(422);
        }

        $query->whereNotIn('pallets.status', ['in-process', 'not-received', 'returned', 'incident']);

        foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            $query->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }


        $query->whereNotNull('pallets.slug');


        $query->defaultSort('pallets.id')
            ->select(
                'pallets.id',
                'pallets.slug',
                'pallets.reference',
                'pallets.customer_reference',
                'pallets.notes',
                'pallets.state',
                'pallets.status',
                'pallets.rental_id',
                'pallets.type',
                'pallets.received_at',
                'pallets.location_id',
                'pallets.fulfilment_customer_id',
                'pallets.warehouse_id',
                'pallets.pallet_delivery_id',
                'pallets.pallet_return_id'
            );


        if ($parent instanceof Fulfilment) {
            $query->leftJoin('fulfilment_customers', 'fulfilment_customers.id', 'pallets.fulfilment_customer_id');
            $query->leftJoin('customers', 'customers.id', 'fulfilment_customers.customer_id');
            $query->addSelect('customers.name as fulfilment_customer_name', 'customers.slug as fulfilment_customer_slug');
        }


        return $query->allowedSorts(['customer_reference', 'reference', 'fulfilment_customer_name'])
            ->allowedFilters([$globalSearch, 'customer_reference', 'reference'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(FulfilmentCustomer|Fulfilment|Location $parent, $prefix = null, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($prefix, $modelOperations, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements']
                );
            }


            $emptyStateData = [
                'icons' => ['fal fa-pallet'],
                'title' => __('No pallets found'),
                'count' => match (class_basename($parent)) {
                    'FulfilmentCustomer' => $parent->number_pallets,
                    default              => $parent->stats->number_pallets
                }
            ];


            if ($parent instanceof Fulfilment) {
                $emptyStateData['description'] = __("There is not pallets in this fulfilment shop");
            } else {
                $emptyStateData['description'] = __("This customer don't have any pallets");
            }

            $table->withGlobalSearch();


            $table->withEmptyState($emptyStateData)
                ->withModelOperations($modelOperations);

            if ($parent->state == PalletDeliveryStateEnum::IN_PROCESS) {
                $table->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true);
            } else {
                $table->column(key: 'type_icon', label: ['fal', 'fa-yin-yang'], type: 'icon');
            }

            $table->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon');


            if ($parent instanceof Organisation || $parent instanceof Fulfilment) {
                $table->column(key: 'fulfilment_customer_name', label: __('Customer'), canBeHidden: false, sortable: true, searchable: true);
            }
            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'customer_reference', label: __("Pallet reference (customer's), notes"), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'contents', label: __('Contents'), canBeHidden: false, searchable: true);


            $table->defaultSort('reference');
        };
    }


    public function jsonResponse(LengthAwarePaginator $pallets): AnonymousResourceCollection
    {
        return PalletsResource::collection($pallets);
    }


    public function htmlResponse(LengthAwarePaginator $pallets, ActionRequest $request): Response
    {
        $subNavigation = [];

        if ($this->parent instanceof Fulfilment) {
            $stats = $this->parent->stats;
        } else {
            $subNavigation = $this->getFulfilmentCustomerSubNavigation($this->parent, $request);
            $stats         = $this->parent;
        }


        return Inertia::render(
            'Org/Fulfilment/Pallets',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Returned Pallets'),
                'pageHead'    => [
                    'title' => $this->parent instanceof Fulfilment ? __('Returned Pallets') : __('Customer Pallets'),
                    'icon'  => [
                        'icon' => ['fal', 'fa-pallet'],
                    ],
                    // 'model' => __('Returned'),

                    'subNavigation' => $subNavigation,
                    'actions'       => [
                        [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('New Delivery'),
                            'route' => [
                                'name'       => 'grp.org.warehouses.show.fulfilment.pallets.create',
                                'parameters' => [
                                    'organisation' => $request->route('organisation'),
                                    'warehouse'    => $request->route('warehouse'),
                                    'fulfilment'   => $request->route('fulfilment')
                                ]
                            ]
                        ],
                    ],

                    'meta' => [
                        [
                            'label'    => __('Returned pallets'),
                            'number'   => $stats->number_pallets_state_dispatched,
                            'href'     => [
                                'name'       => 'grp.org.fulfilments.show.operations.returned_pallets.index',
                                'parameters' => $request->route()->originalParameters()
                            ],
                            'leftIcon' => PalletStateEnum::stateIcon()[PalletStateEnum::DISPATCHED->value]
                        ],
                        [
                            'label'    => __('Damaged pallets'),
                            'number'   => $stats->number_pallets_state_damaged,
                            'href'     => [
                                'name'       => 'grp.org.fulfilments.show.operations.returned_pallets.index',
                                'parameters' => $request->route()->originalParameters()
                            ],
                            'leftIcon' => PalletStateEnum::stateIcon()[PalletStateEnum::DAMAGED->value]
                        ],

                        [
                            'label'    => __('Lost pallets'),
                            'number'   => $stats->number_pallets_state_lost,
                            'href'     => [
                                'name'       => 'grp.org.fulfilments.show.operations.returned_pallets.index',
                                'parameters' => $request->route()->originalParameters()
                            ],
                            'leftIcon' => PalletStateEnum::stateIcon()[PalletStateEnum::LOST->value]
                        ],

                    ]
                ],
                'data'        => PalletsResource::collection($pallets),
            ]
        )->table($this->tableStructure($this->parent, 'pallets'));
    }

    public function asController(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment, 'pallets');
    }

    public function inLocation(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, Location $location, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $location;
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($location, 'pallets');
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilmentCustomer, 'pallets');
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return match ($routeName) {
            'grp.org.fulfilments.show.crm.customers.show.pallets.index' =>
            array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallets.index',
                                'parameters' => $routeParameters,
                            ],
                            'label' => __('Pallets'),
                            'icon'  => 'fal fa-bars',
                        ],

                    ]
                ]
            ),
            default => array_merge(
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
                            'label' => __('Pallets'),
                            'icon'  => 'fal fa-bars',
                        ],

                    ]
                ]
            )
        };
    }
}
