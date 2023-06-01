<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 29 May 2023 12:18:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\UI\ShowShop;
use App\Actions\UI\Dashboard\Dashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Marketing\ShopResource;
use App\Http\Resources\Marketing\WebsiteResource;
use App\InertiaTable\InertiaTable;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Tenant;
use App\Models\Web\Website;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexWebsites extends InertiaAction
{
    public function handle(Tenant|Shop $parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('websites.name', 'LIKE', "%$value%")
                    ->orWhere('websites.code', 'LIKE', "%$value%");
            });
        });
        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::WEBSITES->value);

        return QueryBuilder::for(Website::class)
            ->defaultSort('websites.code')
            ->select(['websites.code',  'websites.name', 'websites.slug'])
            ->when(true, function ($query) use ($parent) {
                if (class_basename($parent) == 'Shop') {
                    $query->where('websites.shop_id', $parent->id);
                    $query->leftJoin('shops', 'shops.id', 'websites.shop_id');
                    $query->addSelect('shops.slug as shop_slug');
                }
            })
            ->allowedSorts(['code', 'name'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::WEBSITES->value.'Page'
            )

            ->withQueryString();
    }

    public function tableStructure($parent): Closure
    {
        return function (InertiaTable $table) use ($parent) {
            $table
                ->name(TabsAbbreviationEnum::WEBSITES->value)
                ->pageName(TabsAbbreviationEnum::WEBSITES->value.'Page');
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
                $request->user()->hasPermissionTo('webpages.view')
            );
    }


    public function jsonResponse(): AnonymousResourceCollection
    {
        return ShopResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $websites, ActionRequest $request): Response
    {
        $parent = $request->route()->parameters() == [] ? app('currentTenant') : last($request->route()->parameters());

        return Inertia::render(
            'Web/Websites',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('websites'),
                'pageHead'    => [
                    'title' => __('websites'),
                ],
                'data'        => WebsiteResource::collection($websites),

            ]
        )->table($this->tableStructure($parent));
    }


    public function asController(): LengthAwarePaginator
    {
        return $this->handle(app('currentTenant'));
    }

    public function inShop(Shop $shop): LengthAwarePaginator
    {
        return $this->handle($shop);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('websites'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'websites.index' =>
            array_merge(
                Dashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'websites.index',
                        null
                    ]
                ),
            ),


            'shops.show.websites.index' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs(
                    ['shop'=>$routeParameters['shop']]
                ),
                $headCrumb(
                    [
                        'name'       => 'shops.show.websites.index',
                        'parameters' =>
                            [
                                $routeParameters['shop']->slug
                            ]
                    ]
                )
            ),
            default => []
        };
    }
}
