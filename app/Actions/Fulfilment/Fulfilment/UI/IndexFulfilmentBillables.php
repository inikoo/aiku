<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 15:15:33 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment\UI;

use App\Actions\OrgAction;
use App\Enums\Catalogue\Billable\BillableStateEnum;
use App\Enums\Catalogue\Billable\BillableTypeEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\UI\Fulfilment\FulfilmentBillablesTabsEnum;
use App\Http\Resources\Fulfilment\FulfilmentProductsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Catalogue\Billable;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexFulfilmentBillables extends OrgAction
{
    protected function getElementGroups(Fulfilment $parent): array
    {
        return [
            'type'  => [
                'label'    => __('Type'),
                'elements' => array_merge_recursive(
                    BillableTypeEnum::labels($parent->shop),
                    BillableTypeEnum::count($parent->shop)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('type', $elements);
                }

            ],
            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    BillableStateEnum::labels(),
                    BillableStateEnum::count($parent->shop)
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
                $query->whereAnyWordStartWith('products.name', $value)
                    ->orWhereStartWith('products.code', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Billable::class);
        $queryBuilder->where('products.shop_id', $parent->shop_id);

        foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        $queryBuilder
            ->defaultSort('products.code')
            ->select([
                'products.code',
                'products.name',
                'products.state',
                'products.type',
                'products.created_at',
                'products.updated_at',
                'products.slug'
            ])
            ->leftJoin('product_stats', 'products.id', 'product_stats.product_id');


        return $queryBuilder->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }


    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("fulfilment-shop.{$this->organisation->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("fulfilment-shop.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->organisation->id}.view");
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(FulfilmentBillablesTabsEnum::values());

        return $this->handle($fulfilment, FulfilmentBillablesTabsEnum::BILLABLES->value);
    }


    public function htmlResponse(LengthAwarePaginator $products, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Fulfilment/Products',
            [
                'title'       => __('fulfilment'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'icon'      => [
                        'icon'  => ['fal', 'fa-ballot'],
                        'title' => __('Billables')
                    ],
                    'title' => __('Billables'),
                ],

                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => FulfilmentBillablesTabsEnum::navigation()
                ],


                FulfilmentBillablesTabsEnum::BILLABLES->value => $this->tab == FulfilmentBillablesTabsEnum::BILLABLES->value ?
                    fn () => FulfilmentProductsResource::collection($products)
                    : Inertia::lazy(fn () => FulfilmentProductsResource::collection($products)),


            ]
        )->table(
            $this->tableStructure(
                parent: $this->fulfilment,
                prefix: FulfilmentBillablesTabsEnum::BILLABLES->value
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
                            'title' => __("No products found"),
                            'count' => $parent->shop->stats->number_products,
                        ],
                        default => null
                    }
                );

            $table
                ->column(key: 'type', label: '', canBeHidden: false, type: 'icon')
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'price', label: __('price'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }


    public function jsonResponse(Fulfilment $fulfilment): FulfilmentProductsResource
    {
        return new FulfilmentProductsResource($fulfilment);
    }


    public function getBreadcrumbs(array $routeParameters, $suffix = null, $icon = 'fal fa-bars'): array
    {
        $headCrumb = function (array $routeParameters = []) use ($suffix, $icon) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Billables'),
                        'icon'  => $icon
                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        return
            array_merge(
                ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.fulfilments.show.billables.index',
                        'parameters' => $routeParameters
                    ]
                )
            );
    }

    public function getPrevious(Fulfilment $fulfilment, ActionRequest $request): ?array
    {
        $previous = Shop::where('organisation_id', $this->organisation->id)->where('type', ShopTypeEnum::FULFILMENT)->where('code', '<', $fulfilment->shop->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous?->fulfilment, $request->route()->getName());
    }

    public function getNext(Fulfilment $fulfilment, ActionRequest $request): ?array
    {
        $next = Shop::where('organisation_id', $this->organisation->id)->where('type', ShopTypeEnum::FULFILMENT)->where('code', '>', $fulfilment->shop->code)->orderBy('code')->first();

        return $this->getNavigation($next?->fulfilment, $request->route()->getName());
    }

    private function getNavigation(?Fulfilment $fulfilment, string $routeName): ?array
    {
        if (!$fulfilment) {
            return null;
        }

        return match ($routeName) {
            'grp.org.fulfilments.show.operations.dashboard' => [
                'label' => $fulfilment->shop?->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'fulfilment'   => $fulfilment->slug
                    ]

                ]
            ]
        };
    }
}
