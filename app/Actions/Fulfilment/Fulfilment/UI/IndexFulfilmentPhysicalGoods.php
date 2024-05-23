<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 May 2024 09:45:43 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment\UI;

use App\Actions\OrgAction;
use App\Enums\Catalogue\Outer\PhysicalGoodStateEnum;
use App\Enums\UI\Fulfilment\PhysicalGoodsTabsEnum;
use App\Http\Resources\Fulfilment\PhysicalGoodsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Outer;
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

class IndexFulfilmentPhysicalGoods extends OrgAction
{
    protected function getElementGroups(Fulfilment $parent): array
    {
        return [

            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    PhysicalGoodStateEnum::labels(),
                    PhysicalGoodStateEnum::count($parent->shop)
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
                $query->whereAnyWordStartWith('outers.name', $value)
                    ->orWhereStartWith('outers.code', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Outer::class);
        $queryBuilder->where('outers.shop_id', $parent->shop_id);
        $queryBuilder->join('products', 'outers.product_id', '=', 'products.id');
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
            ->defaultSort('outers.id')
            ->select([
                'outers.id',
                'outers.state',
                'outers.created_at',
                'outers.price',
                'outers.unit',
                'products.name',
                'products.code',
                'products.main_outerable_price',
                'products.description',
                'currencies.code as currency_code',
            ]);


        return $queryBuilder->allowedSorts(['id','code','name','price'])
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
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(PhysicalGoodsTabsEnum::values());

        return $this->handle($fulfilment, PhysicalGoodsTabsEnum::PHYSICAL_GOODS->value);
    }

    public function htmlResponse(LengthAwarePaginator $physicalGoods, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Fulfilment/PhysicalGoods',
            [
                'title'       => __('fulfilment'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'title'   => __('Physical goods'),
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'primary',
                            'icon'  => 'fal fa-plus',
                            'label' => __('create good'),
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.products.physical_goods.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ],
                    ]
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => PhysicalGoodsTabsEnum::navigation()
                ],



                PhysicalGoodsTabsEnum::PHYSICAL_GOODS->value => $this->tab == PhysicalGoodsTabsEnum::PHYSICAL_GOODS->value ?
                    fn () => PhysicalGoodsResource::collection($physicalGoods)
                    : Inertia::lazy(fn () => PhysicalGoodsResource::collection($physicalGoods)),

            ]
        )->table(
            $this->tableStructure(
                parent: $this->fulfilment,
                prefix: PhysicalGoodsTabsEnum::PHYSICAL_GOODS->value
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
                ->column(key: 'workflow', label: __('workflow'))
                ->defaultSort('code');
        };
    }


    public function jsonResponse(LengthAwarePaginator $physicalGoods): AnonymousResourceCollection
    {
        return PhysicalGoodsResource::collection($physicalGoods);
    }


    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (array $routeParameters = []) use ($suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('services'),
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
