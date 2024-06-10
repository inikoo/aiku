<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:37:19 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\ProductCategory\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\Catalogue\WithDepartmentSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HaCatalogueAuthorisation;
use App\Enums\Catalogue\ProductCategory\ProductCategoryStateEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Http\Resources\Catalogue\SubDepartmentsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexSubDepartment extends OrgAction
{
    use HaCatalogueAuthorisation;
    use WithDepartmentSubNavigation;

    private Shop|ProductCategory|Organisation $parent;

    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle(parent: $organisation);
    }

    /** @noinspection PhpUnusedParameterInspection */
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

        foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }



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


        if (class_basename($parent) == 'Shop') {
            $queryBuilder->where('product_categories.shop_id', $parent->id);
        } elseif (class_basename($parent) == 'Organisation') {
            $queryBuilder->where('product_categories.organisation_id', $parent->id);
            $queryBuilder->leftJoin('shops', 'product_categories.shop_id', 'shops.id');
            $queryBuilder->addSelect(
                'shops.slug as shop_slug',
                'shops.code as shop_code',
                'shops.name as shop_name',
            );
        } elseif (class_basename($parent) == 'ProductCategory') {



            if($parent->type==ProductCategoryTypeEnum::DEPARTMENT) {

                $queryBuilder->where('product_categories.department_id', $parent->id);

            } else {
                // todo
                abort(419);
            }

        }

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
            ->where('product_categories.type', ProductCategoryTypeEnum::SUB_DEPARTMENT)
            ->leftjoin('product_categories as departments', 'departments.id', 'product_categories.department_id')
            ->allowedSorts(['code', 'name', 'shop_code', 'department_code'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Shop|ProductCategory|Organisation $parent, ?array $modelOperations = null, $prefix = null, $canEdit = false): Closure
    {
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
                ->defaultSort('code')
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Organisation' => [
                            'title'       => __("No families found"),
                            'description' => $canEdit ?
                                $parent->catalogueStats->number_shops == 0 ? __("In fact, is no even a shop yet 🤷🏽‍♂️") : ''
                                : '',
                            'count'       => $parent->catalogueStats->number_families,
                            'action'      => $canEdit && $parent->catalogueStats->number_shops == 0
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
                            'title' => __("No families found"),
                            'count' => $parent->stats->number_families,
                        ],
                        'ProductCategory' => [
                            'title'       => __("No sub department found"),
                            'count'       => $parent->stats->number_sub_departments,
                            'action'      => [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new sub department'),
                                'label'   => __('sub department'),
                                'route'   => [
                                    'name'           => 'grp.org.shops.show.catalogue.departments.show.sub-departments.create',
                                    'parameters'     => [
                                        'organisation' => $parent->organisation->slug,
                                        'shop'         => $parent->shop->slug,
                                        'department'   => $parent->slug
                                    ]
                                ]
                            ]
                        ],
                        default => null
                    }
                )
                ->withGlobalSearch()
                ->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon')
                ->withModelOperations($modelOperations);


            if ($parent instanceof Organisation) {
                $table->column(key: 'shop_code', label: __('shop'), canBeHidden: false, sortable: true, searchable: true);
                $table->column(key: 'department_code', label: __('department'), canBeHidden: false, sortable: true, searchable: true);

            }



            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $subDepartment): AnonymousResourceCollection
    {
        return SubDepartmentsResource::collection($subDepartment);
    }

    public function htmlResponse(LengthAwarePaginator $subDepartment, ActionRequest $request): Response
    {
        $subNavigation = null;
        if ($this->parent instanceof ProductCategory) {
            if ($this->parent->type == ProductCategoryTypeEnum::DEPARTMENT) {
                $subNavigation = $this->getDepartmentSubNavigation($this->parent);
            }
        }


        return Inertia::render(
            'Org/Catalogue/SubDepartments',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('sub departments'),
                'pageHead'    => [
                    'title'         => __('sub departments'),
                    'icon'          => [
                        'icon'  => ['fal', 'fa-folder'],
                        'title' => __('sub department')
                    ],
                    'actions'       => [
                        $this->canEdit ? (
                            class_basename($this->parent) == 'ProductCategory'
                            ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new sub department'),
                            'label'   => __('sub department'),
                            'route'   => [
                                'name'       => 'grp.org.shops.show.catalogue.departments.show.sub-departments.create',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ]
                            : (class_basename($this->parent) == 'Shop' ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new sub department'),
                            'label'   => __('sub department'),
                            'route'   => [
                                'name'       => 'grp.org.shops.show.catalogue.families.create',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false)
                        ) : false,
                    ],
                    'subNavigation' => $subNavigation,
                ],
                'data'        => SubDepartmentsResource::collection($subDepartment),
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
                        'label' => __('Sub departments'),
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
            'grp.org.shops.show.catalogue.departments.show.sub-departments.index' => array_merge(
                ShowDepartment::make()->getBreadcrumbs('grp.org.shops.show.catalogue.departments.show', $routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.catalogue.departments.show.sub-departments.index',
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

    protected function getElementGroups($parent): array
    {
        return
            [
                'state' => [
                    'label'    => __('State'),
                    'elements' => array_merge_recursive(
                        ProductCategoryStateEnum::labels(),
                        ProductCategoryStateEnum::countSubDepartment($parent)
                    ),
                    'engine'   => function ($query, $elements) {
                        $query->whereIn('product_categories.state', $elements);
                    }
                ]
            ];
    }
}
