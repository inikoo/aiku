<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:37:19 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory\UI;

use App\Actions\Catalogue\HasMarketAuthorisation;
use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Http\Resources\Catalogue\FamiliesResource;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexFamilies extends OrgAction
{
    use HasMarketAuthorisation;

    private Shop|ProductCategory|Organisation $parent;

    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);
        return $this->handle(parent: $organisation);
    }

    public function inDepartment(Organisation $organisation, Shop $shop, ProductCategory $department, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $department;
        $this->initialisationFromShop($shop, $request);
        return $this->handle(parent: $department);
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);
        return $this->handle(parent: $shop);
    }


    public function handle(Shop|ProductCategory|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('product_categories.name', $value)
                    ->orWhereStartWith('product_categories.code', $value);
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(ProductCategory::class);

        /*
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }
        */

        return $queryBuilder
            ->defaultSort('product_categories.code')
            ->select([
                'product_categories.slug',
                'product_categories.code',
                'product_categories.name',
                'product_categories.state',
                'product_categories.description',
                'product_categories.created_at',
                'product_categories.updated_at',
                'departments.slug as department_slug',
                'departments.code as department_code',
                'departments.name as department_name',

            ])
            ->leftJoin('product_category_stats', 'product_categories.id', 'product_category_stats.product_category_id')
            ->where('product_categories.type', ProductCategoryTypeEnum::FAMILY)
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Shop') {
                    $query->where('product_categories.parent_type', 'Shop');
                    $query->where('product_categories.parent_id', $parent->id);
                } elseif (class_basename($parent) == 'Organisation') {
                    $query->where('product_categories.organisation_id', $parent->id);
                    $query->leftJoin('shops', 'product_categories.shop_id', 'shops.id');
                    $query->addSelect(
                        'shops.slug as shop_slug',
                        'shops.code as shop_code',
                        'shops.name as shop_name',
                    );
                } elseif (class_basename($parent) == 'ProductCategory') {
                    // Handle when parent type is ProductCategory
                    $query->where('product_categories.parent_type', 'ProductCategory');
                    $query->where('product_categories.parent_id', $parent->id);
                }
            })->leftjoin('product_categories as departments', 'departments.id', 'product_categories.parent_id')

            ->allowedSorts(['code', 'name','shop_code','department_code'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Shop|ProductCategory|Organisation $parent, ?array $modelOperations = null, $prefix = null, $canEdit=false): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix, $canEdit) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table
                ->defaultSort('code')
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Organisation' => [
                            'title'       => __("No families found"),
                            'description' => $canEdit ?
                                $parent->marketStats->number_shops == 0 ? __("In fact, is no even a shop yet 🤷🏽‍♂️") : ''
                                : '',
                            'count'       => $parent->marketStats->number_families,
                            'action'      => $canEdit && $parent->marketStats->number_shops == 0
                                    ?
                                [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new shop'),
                                'label'   => __('shop'),
                                'route'   => [
                                    'name'       => 'grp.org.shops.show.catalogue.families.create',
                                    'parameters' => [$parent->slug]
                                ]
                                    ] : null

                        ],
                        'Shop' => [
                            'title'       => __("No families found"),
                            'count'       => $parent->stats->number_families,
                        ],
                        default => null
                    }
                )
                ->withGlobalSearch()
                ->withModelOperations($modelOperations);

            if($parent instanceof Organisation) {
                $table->column(key: 'shop_code', label: __('shop'), canBeHidden: false, sortable: true, searchable: true);
            };
            $table->column(key: 'department_code', label: __('department'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
            ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $families): AnonymousResourceCollection
    {
        return FamiliesResource::collection($families);
    }

    public function htmlResponse(LengthAwarePaginator $families, ActionRequest $request): Response
    {

        $scope    =$this->parent;
        $container=null;
        if (class_basename($scope) == 'Shop') {
            $container = [
                'icon'    => ['fal', 'fa-store-alt'],
                'tooltip' => __('Shop'),
                'label'   => Str::possessive($scope->name)
            ];
        }

        return Inertia::render(
            'Org/Catalogue/Families',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('families'),
                'pageHead'    => [
                    'title'        => __('families'),
                    'container'    => $container,
                    'icon'         => [
                        'icon'  => ['fal', 'fa-folder'],
                        'title' => __('family')
                    ],
                    'actions' => [
                        $this->canEdit && (class_basename($this->parent) == 'ProductCategory' || class_basename($this->parent) == 'Shop') ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new family'),
                            'label'   => __('family'),
                            'route'   => [
                                'name'       => 'grp.org.shops.show.catalogue.families.create',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,
                    ]
                ],
                'data'        => FamiliesResource::collection($families),
            ]
        )->table($this->tableStructure($this->parent));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters, ?string $suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Families'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ]
            ];
        };
    
        return match ($routeName) {
            'grp.org.shops.show.catalogue.families.index' => array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),
            'grp.org.shops.show.catalogue.departments.families.index' => array_merge(
                ShowDepartment::make()->getBreadcrumbs('grp.org.shops.show.catalogue.departments.show', $routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.catalogue.departments.families.index',
                        'parameters' => [
                            $routeParameters['organisation'],
                            $routeParameters['shop'],
                            $routeParameters['department']
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }
}
