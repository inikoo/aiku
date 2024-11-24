<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 20-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Billables\Service\UI;

use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentAssets;
use App\Actions\OrgAction;
use App\Enums\Billables\Service\ServiceStateEnum;
use App\Enums\UI\Fulfilment\ServicesTabsEnum;
use App\Http\Resources\Catalogue\ServicesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Billables\Service;
use App\Models\Catalogue\Shop;
use App\Models\Shop\ShopCustomer;
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


    // public function authorize(ActionRequest $request): bool
    // {
    //     // dd($this->shop);
    //     $this->canEdit   = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    //     $this->canDelete = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    //     return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
    // }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromShop($shop, $request)->withTab(ServicesTabsEnum::values());

        return $this->handle($shop, ServicesTabsEnum::SERVICES->value);
    }

    public function fromRetina(ActionRequest $request): LengthAwarePaginator
    {
        /** @var ShopCustomer $shopCustomer */
        $shopCustomer = $request->user()->customer->shopCustomer;
        $this->shop   = $shopCustomer->shop;

        $this->initialisation($request->get('website')->organisation, $request);
        return $this->handle($this->shop, ServicesTabsEnum::SERVICES->value);
    }

    public function htmlResponse(LengthAwarePaginator $services, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Shop/Services',
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
                    'model'         => __('Billables'),
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
                IndexFulfilmentAssets::make()->getBreadcrumbs(routeParameters: $routeParameters, icon: 'fal fa-ballot'),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.billables.services.index',
                        'parameters' => $routeParameters
                    ]
                )
            );
    }


}