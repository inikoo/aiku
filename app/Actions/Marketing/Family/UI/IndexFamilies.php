<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 11:41:18 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Family\UI;

use App\Actions\InertiaAction;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Marketing\FamilyResource;
use App\Models\Central\Tenant;
use App\Models\Marketing\Department;
use App\Models\Marketing\Family;
use App\Models\Marketing\Shop;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexFamilies extends InertiaAction
{
    use HasUIFamilies;


    public function handle(Shop|Tenant|Department $parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('families.name', '~*', "\y$value\y")
                    ->orWhere('families.code', '=', $value);
            });
        });
        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::FAMILY->value);

        return QueryBuilder::for(Family::class)
            ->defaultSort('families.code')
            ->select(['families.code', 'families.name', 'families.state', 'families.created_at', 'families.updated_at', 'families.slug', 'shops.slug as shop_slug'])
            ->leftJoin('family_stats', 'families.id', 'family_stats.family_id')
            ->leftJoin('shops', 'families.shop_id', 'shops.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Shop') {
                    $query->where('families.shop_id', $parent->id);
                } elseif (class_basename($parent) == 'Department') {
                    $query->where('families.department_id', $parent->id);
                }
            })
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::FAMILY->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent): Closure
    {
        return function (InertiaTable $table) use ($parent) {
            $table
                ->name(TabsAbbreviationEnum::FAMILY->value)
                ->pageName(TabsAbbreviationEnum::FAMILY->value.'Page');

            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true);


            $table->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
        };
    }
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops.families.edit');
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.products.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $families): AnonymousResourceCollection
    {
        return FamilyResource::collection($families);
    }


    public function htmlResponse(LengthAwarePaginator $families, ActionRequest $request)
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());
        return Inertia::render(
            'Marketing/Families',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $parent
                ),
                'title'       => __('families'),
                'pageHead'    => [
                    'title'   => __('families'),
                    'create'  => $this->canEdit && $this->routeName=='shops.show.families.index' ? [
                        'route' => [
                            'name'       => 'shops.show.families.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('family')
                    ] : false,
                ],
                'families' => FamilyResource::collection($families),


            ]
        )->table($this->tableStructure($parent));
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        $this->routeName = $request->route()->getName();
        return $this->handle(app('currentTenant'));
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($shop);
    }

    public function inShopInDepartment(Shop $shop, Department $department, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($department);
    }
}
