<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 May 2024 09:45:43 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment\UI;

use App\Actions\OrgAction;
use App\Enums\Catalogue\Service\ServiceStateEnum;
use App\Enums\UI\Fulfilment\ServicesTabsEnum;
use App\Http\Resources\Fulfilment\ServicesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Service;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexFulfilmentServices extends OrgAction
{
    protected function getElementGroups(Fulfilment $parent): array
    {
        return [

            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    ServicestateEnum::labels(),
                    ServicestateEnum::count($parent->shop)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('state', $elements);
                }

            ],
        ];
    }

    public function handle(Fulfilment $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('services.name', $value)
                    ->orWhereStartWith('services.code', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Service::class);
        $queryBuilder->where('services.shop_id', $parent->shop_id);
        $queryBuilder->join('products', 'services.product_id', '=', 'products.id');
        $queryBuilder->join('currencies', 'products.currency_id', '=', 'currencies.id');




        foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        $queryBuilder
            ->defaultSort('services.id')
            ->select([
                'services.id',
                'services.state',
                'services.created_at',
                'services.price',
                'services.unit',
                'products.name',
                'products.code',
                'products.main_outerable_price',
                'products.description',
                'currencies.code as currency_code',
            ]);


        return $queryBuilder->allowedSorts(['id','price','name','state'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(ServicesTabsEnum::values());

        return $this->handle($fulfilment, ServicesTabsEnum::SERVICES->value);
    }

    public function htmlResponse(LengthAwarePaginator $services, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Fulfilment/Services',
            [
                'title'       => __('fulfilment'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'icon'    => [
                        'icon'  => ['fal', 'fa-concierge-bell'],
                        'title' => __('services')
                    ],
                    'title'   => __('services'),
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'primary',
                            'icon'  => 'fal fa-plus',
                            'label' => __('Create service'),
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.products.services.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ],
                    ]
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => ServicesTabsEnum::navigation()
                ],

                ServicesTabsEnum::SERVICES->value => $this->tab == ServicesTabsEnum::SERVICES->value ?
                    fn () => ServicesResource::collection($services)
                    : Inertia::lazy(fn () => ServicesResource::collection($services)),

            ]
        )->table(
            $this->tableStructure(
                parent: $this->fulfilment,
                prefix: ServicesTabsEnum::SERVICES->value
            )
        );
    }

    public function tableStructure(
        Fulfilment $parent,
        ?array $modelOperations = null,
        $prefix = null,
        $canEdit = false
    ): Closure {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix, $canEdit) {
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

            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Fulfilment' => [
                            'title' => __("No services found"),
                            'count' => $parent->shop->stats->number_products_type_service,
                        ],
                        default => null
                    }
                );

            $table
                ->column(key: 'state', label: '', canBeHidden: false, type: 'icon')
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'price', label: __('price'), canBeHidden: false, sortable: true, searchable: true, className: 'text-right font-mono')
                ->column(key: 'workflow', label: __('workflow'), canBeHidden: false, sortable: true, searchable: true, className: 'hello')
                ->defaultSort('code');
        };
    }


    public function jsonResponse(LengthAwarePaginator $services): AnonymousResourceCollection
    {
        return ServicesResource::collection($services);
    }


    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (array $routeParameters = []) use ($suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Services'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        return
            array_merge(
                IndexFulfilmentProducts::make()->getBreadcrumbs(routeParameters: $routeParameters, icon: 'fal fa-cube'),
                $headCrumb(
                    [
                        'name'       => 'grp.org.fulfilments.show.products.services.index',
                        'parameters' => $routeParameters
                    ]
                )
            );
    }


}
