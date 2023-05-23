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
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexDepartments extends InertiaAction
{
    public function handle(Shop|Tenant $parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('departments.code', '~*', "\y$value\y")
                    ->orWhere('departments.name', '=', $value);
            });
        });
        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::DEPARTMENTS->value);

        return QueryBuilder::for(ProductCategory::class)
            ->defaultSort('departments.code')
            ->select([
                'departments.slug',
                'shops.slug as shop_slug',
                'departments.slug as departments_slug',
                'departments.code',
                'departments.name',
                'departments.state',
                'departments.description',
                'departments.created_at',
                'departments.updated_at',
            ])
            ->leftJoin('department_stats', 'departments.id', 'department_stats.department_id')
            ->leftJoin('shops', 'departments.shop_id', 'shops.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Shop') {
                    $query->where('departments.shop_id', $parent->id);
                }
            })
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::DEPARTMENTS->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent): Closure
    {
        return function (InertiaTable $table) use ($parent) {
            $table
                ->name(TabsAbbreviationEnum::DEPARTMENTS->value)
                ->pageName(TabsAbbreviationEnum::DEPARTMENTS->value.'Page');

            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true);


            $table->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops.departments.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.products.view')
            );
    }

    public function inTenant(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(app('currentTenant'));
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($shop);
    }

    public function jsonResponse(LengthAwarePaginator $departments): AnonymousResourceCollection
    {
        return DepartmentResource::collection($departments);
    }


    public function htmlResponse(LengthAwarePaginator $departments, ActionRequest $request)
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());

        return Inertia::render(
            'Marketing/Departments',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('departments'),
                'pageHead'    => [
                    'title'  => __('departments'),
                    'create' => $this->canEdit && $this->routeName == 'shops.show.catalogue.hub.departments.index' ? [
                        'route' => [
                            'name'       => 'shops.show.catalogue.hub.departments.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('department')
                    ] : false,
                ],
                'data'        => DepartmentResource::collection($departments),


            ]
        )->table($this->tableStructure());
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters, ?string $suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('departments'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ]
            ];
        };


        return match ($routeName) {
            'shops.show.catalogue.hub.departments.index' =>
            array_merge(
                CatalogueHub::make()->getBreadcrumbs('shops.show.catalogue.hub', ['shop' => $routeParameters['shop']]),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),

            'catalogue.hub.departments.index' =>
            array_merge(
                CatalogueHub::make()->getBreadcrumbs('catalogue.hub', []),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),

            default => []
        };
    }
}
