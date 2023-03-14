<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 11:41:18 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Family\UI;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\ShowShop;
use App\Http\Resources\Marketing\FamilyResource;
use App\Models\Central\Tenant;
use App\Models\Marketing\Department;
use App\Models\Marketing\Family;
use App\Models\Marketing\Shop;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexFamilies extends InertiaAction
{
    use HasUIFamilies;

    private Shop|Tenant|Department $parent;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('families.name', '~*', "\y$value\y")
                    ->orWhere('families.code', '=', $value);
            });
        });


        return QueryBuilder::for(Family::class)
            ->defaultSort('families.code')
            ->select(['families.code', 'families.name', 'families.state', 'families.created_at', 'families.updated_at', 'families.slug', 'shops.slug as shop_slug'])
            ->leftJoin('family_stats', 'families.id', 'family_stats.family_id')
            ->leftJoin('shops', 'families.shop_id', 'shops.id')
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'Shop') {
                    $query->where('families.shop_id', $this->parent->id);
                } elseif (class_basename($this->parent) == 'Department') {
                    $query->where('families.department_id', $this->parent->id);
                }
            })
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
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


    public function jsonResponse(): AnonymousResourceCollection
    {
        return FamilyResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $families)
    {
        return Inertia::render(
            'Marketing/Families',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('families'),
                'pageHead'    => [
                    'title' => __('families'),
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
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->defaultSort('code');

            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true);


            $table->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
        });
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        //$this->fillFromRequest($request);
        $this->parent    = app('currentTenant');
        $this->initialisation($request);
        $this->routeName = $request->route()->getName();

        return $this->handle();
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle();
    }

    public function inShopInDepartment(Shop $shop, Department $department, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $department;
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle();
    }

}
