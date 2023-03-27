<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 08:13:54 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Department\UI;

use App\Actions\InertiaAction;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Marketing\DepartmentResource;
use App\Models\Central\Tenant;
use App\Models\Marketing\Department;
use App\Models\Marketing\Shop;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexDepartments extends InertiaAction
{
    use HasUIDepartments;


    public function handle(Shop|Tenant $parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('departments.code', '~*', "\y$value\y")
                    ->orWhere('departments.name', '=', $value);
            });
        });
        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::DEPARTMENTS->value);

        return QueryBuilder::for(Department::class)
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
                    $parent
                ),
                'title'       => __('departments'),
                'pageHead'    => [
                    'title'   => __('departments'),
                    'create'  => $this->canEdit && $this->routeName=='shops.show.departments.index' ? [
                        'route' => [
                            'name'       => 'shops.show.departments.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('department')
                    ] : false,
                ],
                'data' => DepartmentResource::collection($departments),


            ]
        )->table($this->tableStructure($parent));
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request);
        return $this->handle(app('currentTenant'));
    }

    public function InShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($shop);
    }
}
