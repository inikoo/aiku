<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Sep 2023 18:42:14 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Banner\UI;

use App\Actions\OrgAction;
use App\Actions\Overview\ShowGroupOverviewHub;
use App\Actions\Web\Website\UI\ShowWebsite;
use App\Enums\Web\Banner\BannerStateEnum;
use App\Http\Resources\Web\BannersResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Banner;
use App\Models\Web\Website;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexBanners extends OrgAction
{
    protected array $elementGroups = [];
    private Group|Fulfilment|Shop $parent;

    protected function getElementGroups(): array
    {
        return
            [
                'state' => [
                    'label'    => __('State'),
                    'elements' => array_merge_recursive(
                        BannerStateEnum::labels(),
                        BannerStateEnum::count()
                    ),

                    'engine' => function ($query, $elements) {
                        $query->whereIn('banners.state', $elements);
                    }
                ]
            ];
    }

    /** @noinspection PhpUndefinedMethodInspection */
    public function handle($prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where('banners.name', "%$value%");
        });

        $stateFilter = AllowedFilter::callback('state', function ($query, $value) {
            $query->where('banners.state', $value);
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Banner::class)
                        ->leftJoin('organisations', 'banners.organisation_id', '=', 'organisations.id')
                        ->leftJoin('shops', 'banners.shop_id', '=', 'shops.id');

        $queryBuilder->select(
            'banners.id',
            'banners.slug',
            'banners.state',
            'banners.name',
            'banners.image_id',
            'banners.date',
            'shops.name as shop_name',
            'shops.slug as shop_slug',
            'organisations.name as organisation_name',
            'organisations.slug as organisation_slug',
        );

        /*        foreach ($this->getElementGroups() as $key => $elementGroup) {
                    $queryBuilder->whereElementGroup(
                        prefix: $prefix,
                        key: $key,
                        allowedElements: array_keys($elementGroup['elements']),
                        engine: $elementGroup['engine']
                    );
                }*/


        return $queryBuilder
            ->defaultSort('-date')
            ->allowedSorts(['name', 'date', 'number_views', 'organisation_name', 'shop_name'])
            ->allowedFilters([$globalSearch, $stateFilter])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function inGroup(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = group();
        $this->initialisationFromGroup(group(), $request);

        return $this->handle($this->parent);
    }

    public function tableStructure(
        Group|Fulfilment|Shop $parent,
        ?array $modelOperations = null,
        $prefix = null,
        $canEdit = false,
        ?array $exportLinks = null
    ): Closure {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix, $canEdit, $exportLinks) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            /*            foreach ($this->getElementGroups() as $key => $elementGroup) {
                            $table->elementGroup(
                                key: $key,
                                label: $elementGroup['label'],
                                elements: $elementGroup['elements']
                            );
                        }*/

            $action = null;

            $description = null;

            $emptyState = [
                'title'       => __('No banners found'),
                'count'       => 0,
                'description' => $description,
                'action'      => $action
            ];


            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->withEmptyState($emptyState)
                ->withExportLinks($exportLinks)
                ->column(key: 'state', label: ['fal', 'fa-yin-yang'], type: 'icon')
                ->column(key: 'name', label: __('name'), sortable: true);
            if ($parent instanceof Group) {
                $table->column(key: 'organisation_name', label: __('organisation'), canBeHidden: false, sortable: true, searchable: true)
                        ->column(key: 'shop_name', label: __('shop'), canBeHidden: false, sortable: true, searchable: true);
            }
            $table->column(key: 'image_thumbnail', label: ['fal', 'fa-image'])
                ->column(key: 'date', label: __('date'), sortable: true)
                ->defaultSort('-date');
        };
    }

    public function asController(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle();
    }

    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilment;
        $this->initialisationFromShop($fulfilment->shop, $request);

        return $this->handle();
    }

    public function jsonResponse(LengthAwarePaginator $banners): AnonymousResourceCollection
    {
        return BannersResource::collection($banners);
    }

    public function htmlResponse(LengthAwarePaginator $banners, ActionRequest $request): Response
    {
        $container = null;
        return Inertia::render(
            'Org/Web/Banners/Banners',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('banners'),
                'pageHead'    => [
                    'title'     => __('banners'),
                    'container' => $container,
                    'iconRight' => [
                        'title' => __('banner'),
                        'icon'  => 'fal fa-sign'
                    ],
                    'actions'   =>
                        [
                            match ($this->parent::class) {
                                Shop::class => [
                                    'type'  => 'button',
                                    'style' => 'create',
                                    'label' => __('Create Banner'),
                                    'route' => [
                                        'name'       => 'grp.org.shops.show.web.banners.create',
                                        'parameters' => $request->route()->originalParameters()
                                    ]
                                ],
                                Fulfilment::class => [
                                    'type'  => 'button',
                                    'style' => 'create',
                                    'label' => __('Create Banner'),
                                    'route' => [
                                        'name'       => 'grp.org.fulfilments.show.web.banners.create',
                                        'parameters' => $request->route()->originalParameters()
                                    ]
                                ],
                                default => null
                            }
                        ]

                ],

                'data' => BannersResource::collection($banners),
            ]
        )->table(
            $this->tableStructure(
                parent: $this->parent,
                canEdit: $this->canEdit,
            )
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('banners'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.overview.web.banners.index' =>
            array_merge(
                ShowGroupOverviewHub::make()->getBreadcrumbs(
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ]
                ),
            ),
            'grp.org.shops.show.web.banners.index' =>
            array_merge(
                ShowWebsite::make()->getBreadcrumbs('Shop', $routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.web.banners.index',
                        'parameters' => $routeParameters
                    ]
                ),
            ),
            'grp.org.fulfilments.show.web.banners.index' =>
            array_merge(
                ShowWebsite::make()->getBreadcrumbs('Fulfilment', $routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.fulfilments.show.web.banners.index',
                        'parameters' => $routeParameters
                    ]
                ),
            ),
            default => []
        };
    }
}
