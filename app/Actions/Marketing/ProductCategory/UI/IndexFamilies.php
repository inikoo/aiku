<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 27 Apr 2023 16:37:19 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\ProductCategory\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Catalogue\CatalogueHub;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Marketing\DepartmentResource;
use App\Models\Marketing\ProductCategory;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Tenant;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexFamilies extends InertiaAction
{
    public function handle(Shop|ProductCategory|Tenant $parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('product_categories.name', $value)
                    ->orWhere('product_categories.code', 'ilike', "$value%");
            });
        });
        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::FAMILIES->value);

        return QueryBuilder::for(ProductCategory::class)
            ->defaultSort('product_categories.code')
            ->select([
                'product_categories.slug',
                'product_categories.code',
                'product_categories.name',
                'product_categories.state',
                'product_categories.description',
                'product_categories.created_at',
                'product_categories.updated_at',
            ])
            ->leftJoin('product_category_stats', 'product_categories.id', 'product_category_stats.product_category_id')
            ->where('is_family', true)
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Shop') {
                    $query->where('product_categories.parent_type', 'Shop');
                    $query->where('product_categories.parent_id', $parent->id);
                } elseif (class_basename($parent) == 'Tenant') {
                    $query->leftJoin('shops', 'product_categories.shop_id', 'shops.id');
                    $query->addSelect('shops.slug as shop_slug');
                }
            })
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::FAMILIES->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent, ?array $modelOperations = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations) {
            $table
                ->defaultSort('code')
                ->name(TabsAbbreviationEnum::FAMILIES->value)
                ->pageName(TabsAbbreviationEnum::FAMILIES->value.'Page')
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('products.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('products.view')
            );
    }

    public function inTenant(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(parent: app('currentTenant'));
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(parent: $shop);
    }

    public function jsonResponse(LengthAwarePaginator $departments): AnonymousResourceCollection
    {
        return DepartmentResource::collection($departments);
    }


    public function htmlResponse(LengthAwarePaginator $departments, ActionRequest $request): Response
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());

        return Inertia::render(
            'Marketing/Departments',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('Departments'),
                'pageHead'    => [
                    'title'  => __('departments'),
                    'create' => $this->canEdit &&
                        $this->routeName == 'catalogue.shop.departments.index'
                    ? [
                        'route' => [
                            'name'       => 'catalogue.shop.departments.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('departments')
                    ] : false,
                ],
                'data'        => DepartmentResource::collection($departments),
            ]
        )->table($this->tableStructure($parent));
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters, ?string $suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('families'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ]
            ];
        };

        return match ($routeName) {
            'catalogue.shop.families.index' =>
            array_merge(
                CatalogueHub::make()->getBreadcrumbs('catalogue.shop.hub', $routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),

            'catalogue.families.index' =>
            array_merge(
                CatalogueHub::make()->getBreadcrumbs('catalogue.hub', []),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => []
                    ],
                    $suffix
                )
            ),

            default => []
        };
    }
}
