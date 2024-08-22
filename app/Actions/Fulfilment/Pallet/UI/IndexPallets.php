<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 18:40:36 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasFulfilmentAssetsAuthorisation;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Models\Fulfilment\Fulfilment;
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
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexPallets extends OrgAction
{
    use HasFulfilmentAssetsAuthorisation;
    use WithPalletsSubNavigation;


    private bool $selectStoredPallets = false;

    private Fulfilment $parent;

    protected function getElementGroups(Fulfilment $fulfilment): array
    {
        return [
            'status' => [
                'label'    => __('Status'),
                'elements' => array_merge_recursive(
                    PalletStatusEnum::labels($fulfilment),
                    PalletStatusEnum::count($fulfilment)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('pallets.status', $elements);
                }
            ],


        ];
    }

    public function handle(Fulfilment $fulfilment, $prefix = null): LengthAwarePaginator
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

        $query->where('pallets.fulfilment_id', $fulfilment->id);

        $query->whereNotIn('pallets.status', ['in-process', 'not-received', 'returned', 'incident']);

        foreach ($this->getElementGroups($fulfilment) as $key => $elementGroup) {
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


        $query->leftJoin('fulfilment_customers', 'fulfilment_customers.id', 'pallets.fulfilment_customer_id');
        $query->leftJoin('customers', 'customers.id', 'fulfilment_customers.customer_id');
        $query->addSelect('customers.name as fulfilment_customer_name', 'customers.slug as fulfilment_customer_slug');


        $query->leftJoin('locations', 'locations.id', 'pallets.location_id');
        $query->addSelect('locations.code as location_code', 'locations.slug as location_slug');

        return $query->allowedSorts(['customer_reference', 'reference', 'fulfilment_customer_name'])
            ->allowedFilters([$globalSearch, 'customer_reference', 'reference'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Fulfilment|Location $fulfilment, $prefix = null, $modelOperations = []): Closure
    {
        return function (InertiaTable $table) use ($prefix, $modelOperations, $fulfilment) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            foreach ($this->getElementGroups($fulfilment) as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements']
                );
            }


            $emptyStateData = [
                'icons' => ['fal fa-pallet'],
                'title' => __('No pallets found'),
                'count' => $fulfilment->stats->number_pallets
            ];


            $emptyStateData['description'] = __("There is not pallets in this fulfilment shop");


            $table->withGlobalSearch();


            $table->withEmptyState($emptyStateData)
                ->withModelOperations($modelOperations);

            $table->column(key: 'type_icon', label: ['fal', 'fa-yin-yang'], type: 'icon');


            $table->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon');


            $table->column(key: 'fulfilment_customer_name', label: __('Customer'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'customer_reference', label: __("Pallet reference (customer's), notes"), shortLabel: 'PR/N', canBeHidden: false, sortable: true, searchable: true);
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


        $subNavigation = $this->getPalletsInWarehouseSubNavigation($this->fulfilment, $request);

        $title      = __('Pallets');
        $icon       = ['fal', 'fa-pallet'];
        $afterTitle = null;
        $iconRight  = null;


        $stats = $this->fulfilment->stats;


        return Inertia::render(
            'Org/Fulfilment/Pallets',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Pallets'),
                'pageHead'    => [
                    'title'      => $title,
                    'afterTitle' => $afterTitle,
                    'iconRight'  => $iconRight,
                    'icon'       => $icon,

                    'subNavigation' => $subNavigation,


                ],
                'data'        => PalletsResource::collection($pallets),
            ]
        )->table($this->tableStructure($this->fulfilment, 'pallets'));
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent=$fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);
        return $this->handle($fulfilment, 'pallets');
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            ShowFulfilment::make()->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.fulfilments.show.operations.pallets.current.index',
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
        );
    }
}
