<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:33 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Shop\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Dashboard\Dashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Marketing\ShopResource;
use App\InertiaTable\InertiaTable;
use App\Models\Marketing\Shop;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @property array $breadcrumbs
 * @property bool $canEdit
 * @property string $title
 */
class IndexShops extends InertiaAction
{
    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('shops.name', $value)
                    ->orWhere('shops.code', 'ilike', "$value%");
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::SHOPS->value);

        return QueryBuilder::for(Shop::class)
            ->defaultSort('shops.code')
            ->select(['code', 'id', 'name', 'slug','type','subtype'])
            ->allowedSorts(['code', 'name','type','subtype'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::SHOPS->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure($parent): Closure
    {
        return function (InertiaTable $table) use ($parent) {
            $table
                ->name(TabsAbbreviationEnum::SHOPS->value)
                ->pageName(TabsAbbreviationEnum::SHOPS->value.'Page');
            $table
                ->withGlobalSearch()
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'subtype', label: __('subtype'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('shops');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.view')
            );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle();
    }

    public function jsonResponse(): AnonymousResourceCollection
    {
        return ShopResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $shops, ActionRequest $request): Response
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());

        return Inertia::render(
            'Marketing/Shops',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('shops'),
                'pageHead'    => [
                    'title'   => __('shops'),
                    'create'  => $this->canEdit && $this->routeName=='shops.index' ? [
                        'route' => [
                            'name'       => 'shops.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('shop')
                    ] : false,
                ],
                'data'       => ShopResource::collection($shops),


            ]
        )->table($this->tableStructure($parent));
    }

    public function getBreadcrumbs($suffix=null): array
    {
        return
            array_merge(
                (new Dashboard())->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'shops.index'
                            ],
                            'label' => __('shops'),
                            'icon'  => 'fal fa-bars'
                        ],
                        'suffix'=> $suffix

                    ]
                ]
            );
    }
}
