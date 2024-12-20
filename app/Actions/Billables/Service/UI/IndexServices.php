<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 20-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Billables\Service\UI;

use App\Actions\Billables\UI\ShowBillablesDashboard;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\UI\ShowOverviewHub;
use App\Actions\Traits\Authorisations\HasCatalogueAuthorisation;
use App\Enums\Billables\Service\ServiceStateEnum;
use App\Enums\UI\Fulfilment\ServicesTabsEnum;
use App\Http\Resources\Catalogue\ServicesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Billables\Service;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexServices extends OrgAction
{
    use HasCatalogueAuthorisation;

    protected function getElementGroups(Shop $parent): array
    {

        return [

            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    ServicestateEnum::labels(),
                    ServicestateEnum::count($parent),
                    ServicestateEnum::shortLabels(),
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('state', $elements);
                }

            ],
        ];
    }

    public function handle(Group|Shop $parent, $prefix = null): LengthAwarePaginator
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

        $queryBuilder = QueryBuilder::for(Service::class)
                        ->leftJoin('organisations', 'services.organisation_id', '=', 'organisations.id')
                        ->leftJoin('shops', 'services.shop_id', '=', 'shops.id');
        if ($this->parent instanceof Group) {
            $queryBuilder->where('services.group_id', $parent->id);
        } else {
            $queryBuilder->where('services.shop_id', $parent->id);
        }
        $queryBuilder->join('assets', 'services.asset_id', '=', 'assets.id');
        $queryBuilder->join('currencies', 'assets.currency_id', '=', 'currencies.id');


        if (!($parent instanceof Group)) {
            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                $queryBuilder->whereElementGroup(
                    key: $key,
                    allowedElements: array_keys($elementGroup['elements']),
                    engine: $elementGroup['engine'],
                    prefix: $prefix
                );
            }
        }

        $queryBuilder
            ->defaultSort('services.id')
            ->select([
                'services.id',
                'services.slug',
                'services.state',
                'services.created_at',
                'services.price',
                'services.unit',
                'assets.name',
                'assets.code',
                'assets.current_historic_asset_id as historic_asset_id',
                'services.description',
                'currencies.code as currency_code',
                'shops.name as shop_name',
                'shops.slug as shop_slug',
                'organisations.name as organisation_name',
                'organisations.slug as organisation_slug',
            ]);


        return $queryBuilder->allowedSorts(['code','price','name','state'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }



    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromShop($shop, $request)->withTab(ServicesTabsEnum::values());

        return $this->handle($shop, ServicesTabsEnum::SERVICES->value);
    }

    public function inGroup(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = group();
        $this->initialisationFromGroup(group(), $request)->withTab(ServicesTabsEnum::values());

        return $this->handle(parent: group(), prefix: ServicesTabsEnum::SERVICES->value);
    }


    public function htmlResponse(LengthAwarePaginator $services, ActionRequest $request): Response
    {

        $actions = [
            [
                'type'  => 'button',
                'style' => 'primary',
                'icon'  => 'fal fa-plus',
                'label' => __('Create service'),
                'route' => [
                    'name'       => 'grp.org.shops.show.billables.services.create',
                    'parameters' => array_values($request->route()->originalParameters())
                ]
            ],
        ];

        if ($this->parent instanceof Group) {
            $actions = [];
        }
        return Inertia::render(
            'Org/Billables/Services',
            [
                'title'       => __('shop'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'pageHead'    => array_filter([
                    'icon'    => [
                        'icon'  => ['fal', 'fa-concierge-bell'],
                        'title' => __('services')
                    ],
                    'title'         => __('services'),
                    'actions'       => $actions,
                ]),
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
                parent: $this->shop ?? $this->parent,
                prefix: ServicesTabsEnum::SERVICES->value
            )
        );
    }

    public function tableStructure(
        Group|Shop $parent,
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

            if (!($parent instanceof Group)) {
                foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                    $table->elementGroup(
                        key: $key,
                        label: $elementGroup['label'],
                        elements: $elementGroup['elements']
                    );
                }
            }


            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Shop' => [
                            'title' => __("No services found"),
                            'count' => $parent->stats->number_assets_type_service,
                        ],
                        default => null
                    }
                );

            $table
                ->column(key: 'state', label: '', canBeHidden: false, type: 'icon')
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);

            if ($parent instanceof Group) {
                $table->column(key: 'organisation_name', label: __('organisation'), canBeHidden: false, sortable: true, searchable: true)
                        ->column(key: 'shop_name', label: __('shop'), canBeHidden: false, sortable: true, searchable: true);
            }
            $table->column(key: 'price', label: __('price'), canBeHidden: false, sortable: true, searchable: true, className: 'text-right font-mono')
                ->defaultSort('code');
        };
    }


    public function jsonResponse(LengthAwarePaginator $services): AnonymousResourceCollection
    {
        return ServicesResource::collection($services);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
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

        return match ($routeName) {
            'grp.overview.billables.services.index' =>
            array_merge(
                ShowOverviewHub::make()->getBreadcrumbs(
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => array_merge(
                ShowBillablesDashboard::make()->getBreadcrumbs(routeParameters: $routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.billables.services.index',
                        'parameters' => $routeParameters
                    ]
                )
            )
        };
    }


}
