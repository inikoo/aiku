<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 16:30:07 Central European Summer Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Shop;

use App\Actions\InertiaAction;
use App\Actions\UI\Dashboard\Dashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Marketing\ShopResource;
use App\Models\Marketing\Shop;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
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
                $query->where('shops.name', 'LIKE', "%$value%")
                    ->orWhere('shops.code', 'LIKE', "%$value%");
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::SHOPS->value);

        return QueryBuilder::for(Shop::class)
            ->defaultSort('shops.code')
            ->select(['code', 'id', 'name', 'slug'])
            ->allowedSorts(['code', 'name'])
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
                ->defaultSort('code');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.view')
            );
    }


    public function jsonResponse(): AnonymousResourceCollection
    {
        return ShopResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $shops, ActionRequest $request)
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());

        return Inertia::render(
            'Marketing/Shops',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('shops'),
                'pageHead'    => [
                    'title' => __('shops'),
                ],
                'data'       => ShopResource::collection($shops),


            ]
        )->table($this->tableStructure($parent));
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle();
    }

    public function getBreadcrumbs(): array
    {
        return
            array_merge(
                Dashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'shops.index'
                            ],
                            'label' => __('shops'),
                            'icon'  => 'fal fa-bars'
                        ]
                    ]
                ]
            );
    }
}
