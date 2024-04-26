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
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
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
use App\Models\SysAdmin\User;

class IndexPallets extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;

    private Organisation|FulfilmentCustomer|Location|Fulfilment|Warehouse|PalletDelivery|PalletReturn $parent;

    private bool $selectStoredPallets = false;

    protected function getElementGroups(Organisation|FulfilmentCustomer|Fulfilment|Warehouse|PalletDelivery|PalletReturn $parent): array
    {
        return [
            'status' => [
                'label'    => __('Status'),
                'elements' => array_merge_recursive(
                    PalletStatusEnum::labels(forElements: true),
                    PalletStatusEnum::count($parent, forElements: true)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('pallets.status', $elements);
                }
            ],


        ];
    }

    public function handle(Organisation|FulfilmentCustomer|Location|Fulfilment|Warehouse|PalletDelivery|PalletReturn $parent, $prefix = null): LengthAwarePaginator
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
            case "Location":
                $query->where('location_id', $parent->id);
                break;
            case "Organisation":
                $query->where('pallets.organisation_id', $parent->id);
                break;
            case "Fulfilment":
                $query->where('pallets.fulfilment_id', $parent->id);
                break;
            case "Warehouse":
                $query->where('pallets.warehouse_id', $parent->id);
                break;
            case "PalletDelivery":
                $query->where('pallet_delivery_id', $parent->id);
                break;
            case "PalletReturn":
                $query->where('pallet_return_id', $parent->id);
                break;
            default:
                $query->where('pallets.group_id', app('group')->id);
                break;
        }


        if (!$parent instanceof Location) {
            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                $query->whereElementGroup(
                    key: $key,
                    allowedElements: array_keys($elementGroup['elements']),
                    engine: $elementGroup['engine'],
                    prefix: $prefix
                );
            }
        }


        if ($this->selectStoredPallets) {
            $query->where('pallets.state', PalletStateEnum::STORING);
        }

        if(!$parent instanceof PalletDelivery and !$parent instanceof PalletReturn) {
            $query->whereNotNull('pallets.slug');
        }

        if (!$parent instanceof PalletDelivery) {
            $query->where('pallets.state', '!=', PalletStateEnum::IN_PROCESS);
        }

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

        if (!$parent instanceof Location) {
            $query->leftJoin('locations', 'locations.id', 'pallets.location_id');
            $query->addSelect('locations.code as location_code', 'locations.slug as location_slug', 'locations.id as location_id');
        }
        if ($parent instanceof Fulfilment or $parent instanceof Warehouse or $parent instanceof Organisation) {
            $query->leftJoin('fulfilment_customers', 'fulfilment_customers.id', 'pallets.fulfilment_customer_id');
            $query->leftJoin('customers', 'customers.id', 'fulfilment_customers.customer_id');
            $query->addSelect('customers.name as fulfilment_customer_name', 'customers.slug as fulfilment_customer_slug');
        }

        return $query->allowedSorts(['customer_reference', 'reference', 'fulfilment_customer_name'])
            ->allowedFilters([$globalSearch, 'customer_reference', 'reference'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation|FulfilmentCustomer|Location|Fulfilment|Warehouse|PalletDelivery|PalletReturn $parent, $prefix = null, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($prefix, $modelOperations, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            if (!$parent instanceof Location and !$parent instanceof PalletDelivery and !$parent instanceof PalletReturn) {
                foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                    $table->elementGroup(
                        key: $key,
                        label: $elementGroup['label'],
                        elements: $elementGroup['elements']
                    );
                }
            }


            $emptyStateData = [
                'icons' => ['fal fa-pallet'],
                'title' => '',
                'count' => match (class_basename($parent)) {
                    'FulfilmentCustomer' => $parent->number_pallets,
                    default              => $parent->stats->number_pallets
                }
            ];


            if ($parent instanceof Fulfilment) {
                $emptyStateData['description'] = __("There is not pallets in this fulfilment shop");
            }
            if ($parent instanceof Warehouse) {
                $emptyStateData['description'] = __("There isn't any fulfilment pallet in this warehouse");
            }
            if ($parent instanceof FulfilmentCustomer) {
                $emptyStateData['description'] = __("This customer don't have any pallets");
            }

            if(!$parent instanceof PalletDelivery and !$parent instanceof PalletReturn) {
                $table->withGlobalSearch();
            }

            $table->withEmptyState($emptyStateData)
                ->withModelOperations($modelOperations);

            if($parent->state == PalletDeliveryStateEnum::IN_PROCESS) {
                $table->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true);
            } else {
                $table->column(key: 'type_icon', label: ['fal', 'fa-yin-yang'], type: 'icon');
            }

            if (!($parent instanceof PalletDelivery and $parent->state == PalletDeliveryStateEnum::IN_PROCESS)) {
                $table->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon');
                $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);
            }

            $customersReferenceLabel= __("Pallet reference (customer's), notes");
            if(
                ($parent instanceof PalletDelivery and  $parent->state==PalletDeliveryStateEnum::IN_PROCESS)or ($parent instanceof PalletReturn and $parent->state==PalletReturnStateEnum::IN_PROCESS)
            ) {
                $customersReferenceLabel= __('Customer Reference');
            }


            $table->column(key: 'customer_reference', label: $customersReferenceLabel, canBeHidden: false, sortable: true, searchable: true);


            if ($parent instanceof Organisation || $parent instanceof Fulfilment || $parent instanceof Warehouse) {
                $table->column(key: 'fulfilment_customer_name', label: __('Customer'), canBeHidden: false, sortable: true, searchable: true);
            }


            if(
                ($parent instanceof PalletDelivery and  $parent->state==PalletDeliveryStateEnum::IN_PROCESS)or ($parent instanceof PalletReturn and $parent->state==PalletReturnStateEnum::IN_PROCESS)
            ) {
                $table->column(key: 'notes', label: __('Notes'), canBeHidden: false, searchable: true);

            }



            if (($parent instanceof Organisation or $parent instanceof Fulfilment or $parent instanceof Warehouse or $parent instanceof PalletDelivery or $parent instanceof PalletReturn) and in_array($parent->state, [PalletDeliveryStateEnum::RECEIVED,PalletDeliveryStateEnum::BOOKED_IN, PalletDeliveryStateEnum::BOOKING_IN]) and request(
            )->user() instanceof User) {
                $table->column(key: 'location', label: __('Location'), canBeHidden: false, searchable: true);
                $table->column(key: 'rental', label: __('Rental'), canBeHidden: false, searchable: true);
            }


            if(
                !(
                    ($parent instanceof PalletDelivery and  $parent->state==PalletDeliveryStateEnum::BOOKED_IN) or
                    ($parent instanceof PalletReturn and ($parent->state==PalletReturnStateEnum::DISPATCHED or $parent->state==PalletReturnStateEnum::CANCEL))
                )
            ) {
                $table->column(key: 'actions', label: ' ', canBeHidden: false, searchable: true);

            }


            $table->defaultSort('reference');
        };
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
                    'title'   => __('pallets'),
                    'icon'    => ['fal', 'fa-pallet'],
                    'actions' => [
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

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilmentCustomer, 'pallets');
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($warehouse, 'pallets');
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function fromDelivery(Organisation $organisation, Warehouse $warehouse, PalletDelivery $palletDelivery, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($palletDelivery);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function fromReturn(Organisation $organisation, Warehouse $warehouse, PalletReturn $palletReturn, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($palletReturn);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inLocation(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, Location $location, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $location;
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($location);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return match ($routeName) {
            'grp.org.warehouses.show.fulfilment.pallets.index', 'grp.org.warehouses.show.fulfilment.pallets.show' =>
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
