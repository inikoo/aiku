<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 18 Dec 2024 23:37:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Billables\Services;

use App\Actions\OrgAction;
use App\Enums\UI\Fulfilment\ServicesTabsEnum;
use App\Http\Resources\Catalogue\ServicesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Billables\Service;
use App\Models\Catalogue\Shop;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexServices extends OrgAction
{
    public function handle(Shop $parent, $prefix = null): LengthAwarePaginator
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
        $queryBuilder->join('assets', 'services.asset_id', '=', 'assets.id');
        $queryBuilder->join('currencies', 'assets.currency_id', '=', 'currencies.id');


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
                'services.is_auto_assign',
                'services.auto_assign_trigger',
                'services.auto_assign_subject',
                'services.auto_assign_subject_type',
                'services.auto_assign_status',
            ]);


        return $queryBuilder->allowedSorts(['code','price','name','state'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }




    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        /** @var Shop $shop */
        $shop = $request->user()->customer->shop;


        $this->initialisationFromShop($shop, $request);
        return $this->handle($shop, ServicesTabsEnum::SERVICES->value);
    }

    public function htmlResponse(LengthAwarePaginator $services, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Catalogue/Services',
            [
                'title'       => __('shop'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'icon'    => [
                        'icon'  => ['fal', 'fa-concierge-bell'],
                        'title' => __('services')
                    ],
                    'title'         => __('services'),
                    'actions'       => [
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
                parent: $this->shop,
                prefix: ServicesTabsEnum::SERVICES->value
            )
        );
    }

    public function tableStructure(
        Shop $parent,
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
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'price', label: __('price'), canBeHidden: false, sortable: true, searchable: true, className: 'text-right font-mono')
                ->column(key: 'workflow', label: __('workflow'), canBeHidden: false)
                ->defaultSort('code');
        };
    }



    //todo
    //    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    //    {
    //        $headCrumb = function (array $routeParameters = []) use ($suffix) {
    //            return [
    //                [
    //                    'type'   => 'simple',
    //                    'simple' => [
    //                        'route' => $routeParameters,
    //                        'label' => __('Services'),
    //                        'icon'  => 'fal fa-bars'
    //                    ],
    //                    'suffix' => $suffix
    //                ],
    //            ];
    //        };
    //
    //        return
    //            array_merge(
    //                ShowFulfilmentCatalogueDashboard::make()->getBreadcrumbs(routeParameters: $routeParameters, icon: 'fal fa-ballot'),
    //                $headCrumb(
    //                    [
    //                        'name'       => 'grp.org.shops.show.billables.services.index',
    //                        'parameters' => $routeParameters
    //                    ]
    //                )
    //            );
    //    }


}
