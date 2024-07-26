<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 18:40:36 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\UI;

use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\WithFulfilmentCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\UI\Fulfilment\FulfilmentCustomerPalletsTabsEnum;
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
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexPalletsInCustomer extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;
    use WithFulfilmentCustomerSubNavigation;


    private bool $selectStoredPallets = false;

    private FulfilmentCustomer $parent;

    protected function getElementGroups(FulfilmentCustomer $fulfilmentCustomer, string $prefix): array
    {
        $elements = [];

        if($prefix=='all') {
            $elements = [
                'status' => [
                    'label'    => __('Status'),
                    'elements' => array_merge_recursive(
                        PalletStatusEnum::labels($fulfilmentCustomer),
                        PalletStatusEnum::count($fulfilmentCustomer)
                    ),

                    'engine' => function ($query, $elements) {
                        $query->whereIn('pallets.status', $elements);
                    }
                ],


            ];
        }

        return $elements;
    }

    public function handle(FulfilmentCustomer $fulfilmentCustomer, $prefix = null): LengthAwarePaginator
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


        $query->where('fulfilment_customer_id', $fulfilmentCustomer->id);


        if ($prefix == FulfilmentCustomerPalletsTabsEnum::STORING->value) {
            $query->whereIn('pallets.status', [PalletStatusEnum::STORING, PalletStatusEnum::RETURNING]);
        } elseif ($prefix == FulfilmentCustomerPalletsTabsEnum::INCOMING->value) {
            $query->whereIn('pallets.status', [PalletStatusEnum::IN_PROCESS]);
        } elseif ($prefix == FulfilmentCustomerPalletsTabsEnum::INCIDENT->value) {
            $query->whereIn('pallets.status', [PalletStatusEnum::INCIDENT]);
        } elseif ($prefix == FulfilmentCustomerPalletsTabsEnum::RETURNED->value) {
            $query->whereIn('pallets.status', [PalletStatusEnum::RETURNED]);
        }


        foreach ($this->getElementGroups($fulfilmentCustomer, $prefix) as $key => $elementGroup) {
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


        return $query->allowedSorts(['customer_reference', 'reference', 'fulfilment_customer_name'])
            ->allowedFilters([$globalSearch, 'customer_reference', 'reference'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(FulfilmentCustomer $fulfilmentCustomer, $prefix = null, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($prefix, $modelOperations, $fulfilmentCustomer) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            foreach ($this->getElementGroups($fulfilmentCustomer, $prefix) as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements']
                );
            }


            $emptyStateData = [
                'icons'       => ['fal fa-pallet'],
                'title'       => __('No pallets found'),
                'count'       => $fulfilmentCustomer->number_pallets,
                'description' => __("This customer don't have any pallets")
            ];


            $table->withGlobalSearch();


            $table->withEmptyState($emptyStateData)
                ->withModelOperations($modelOperations);


            $table->column(key: 'type_icon', label: ['fal', 'fa-yin-yang'], type: 'icon');


            $table->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon');


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


        $subNavigation = $this->getFulfilmentCustomerSubNavigation($this->parent, $request);

        $icon       = ['fal', 'fa-user'];
        $title      = $this->parent->customer->name;
        $iconRight  = [
            'icon' => 'fal fa-pallet',
        ];
        $afterTitle = [

            'label' => __('pallets')
        ];


        return Inertia::render(
            'Org/Fulfilment/PalletsInCustomer',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'       => __('Returned Pallets'),
                'pageHead'    => [
                    'title'      => $title,
                    'afterTitle' => $afterTitle,
                    'iconRight'  => $iconRight,
                    'icon'       => $icon,

                    'subNavigation' => $subNavigation,

                    'actions'       => [
                        [
                            'type'    => 'button',
                            'style'   => 'secondary',
                            'tooltip' => __('Audits'),
                            'label'   => __('Audits'),
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.show.stored-item-audits.index',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ],
                    ],
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => FulfilmentCustomerPalletsTabsEnum::navigation(),
                ],

                FulfilmentCustomerPalletsTabsEnum::STORING->value => $this->tab == FulfilmentCustomerPalletsTabsEnum::STORING->value ?
                    fn () => PalletsResource::collection($pallets)
                    : Inertia::lazy(fn () => PalletsResource::collection($pallets)),

                FulfilmentCustomerPalletsTabsEnum::INCOMING->value => $this->tab == FulfilmentCustomerPalletsTabsEnum::INCOMING->value ?
                    fn () => PalletsResource::collection($pallets)
                    : Inertia::lazy(fn () => PalletsResource::collection($pallets)),



                FulfilmentCustomerPalletsTabsEnum::RETURNED->value => $this->tab == FulfilmentCustomerPalletsTabsEnum::RETURNED->value ?
                    fn () => PalletsResource::collection($pallets)
                    : Inertia::lazy(fn () => PalletsResource::collection($pallets)),

                FulfilmentCustomerPalletsTabsEnum::INCIDENT->value => $this->tab == FulfilmentCustomerPalletsTabsEnum::INCIDENT->value ?
                    fn () => PalletsResource::collection($pallets)
                    : Inertia::lazy(fn () => PalletsResource::collection($pallets)),

                FulfilmentCustomerPalletsTabsEnum::ALL->value => $this->tab == FulfilmentCustomerPalletsTabsEnum::ALL->value ?
                    fn () => PalletsResource::collection($pallets)
                    : Inertia::lazy(fn () => PalletsResource::collection($pallets)),


            ]
        )
            ->table($this->tableStructure($this->parent, FulfilmentCustomerPalletsTabsEnum::STORING->value))
            ->table($this->tableStructure($this->parent, FulfilmentCustomerPalletsTabsEnum::INCOMING->value))
            ->table($this->tableStructure($this->parent, FulfilmentCustomerPalletsTabsEnum::RETURNED->value))
            ->table($this->tableStructure($this->parent, FulfilmentCustomerPalletsTabsEnum::INCIDENT->value))
            ->table($this->tableStructure($this->parent, FulfilmentCustomerPalletsTabsEnum::ALL->value))

        ;
    }


    public function asController(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(FulfilmentCustomerPalletsTabsEnum::values());

        return $this->handle($fulfilmentCustomer, $request->get('tab', FulfilmentCustomerPalletsTabsEnum::STORING->value));
    }


    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
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
        );
    }
}
