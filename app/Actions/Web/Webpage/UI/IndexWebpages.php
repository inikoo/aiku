<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Feb 2024 14:48:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage\UI;

use App\Actions\OrgAction;
use App\Actions\Overview\ShowGroupOverviewHub;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Actions\UI\Dashboards\ShowGroupDashboard;
use App\Actions\Web\Webpage\WithWebpageSubNavigation;
use App\Actions\Web\Website\UI\ShowWebsite;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Http\Resources\Web\WebpagesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexWebpages extends OrgAction
{
    use HasWebAuthorisation;
    use WithWebpageSubNavigation;

    private Group|Organisation|Website|Fulfilment|Webpage $parent;

    private mixed $bucket;


    public function inGroup(ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'all';
        $this->parent = group();
        $this->scope  = $this->parent;
        $this->initialisationFromGroup(group(), $request);

        return $this->handle($this->parent);
    }

    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->scope  = $organisation;
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);


        return $this->handle($this->parent);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'all';
        $this->scope  = $fulfilment;
        $this->parent = $website;
        $this->initialisationFromFulfilment($fulfilment, $request);


        return $this->handle($this->parent);
    }

    public function asController(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'all';
        $this->scope  = $shop;
        $this->parent = $website;
        $this->initialisationFromShop($website->shop, $request);


        return $this->handle(parent: $this->parent, bucket: $this->bucket);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function catalogue(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'catalogue';
        $this->scope  = $shop;
        $this->parent = $website;
        $this->initialisationFromShop($website->shop, $request);


        return $this->handle(parent: $this->parent, bucket: $this->bucket);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function content(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'content';
        $this->scope  = $shop;
        $this->parent = $website;
        $this->initialisationFromShop($shop, $request);


        return $this->handle(parent: $this->parent, bucket: $this->bucket);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function contentInFulfilment(Organisation $organisation, Fulfilment $fulfilment, Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'content';
        $this->scope  = $fulfilment;
        $this->parent = $website;
        $this->initialisationFromFulfilment($fulfilment, $request);


        return $this->handle(parent: $this->parent, bucket: $this->bucket);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function info(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'info';
        $this->scope  = $shop;
        $this->parent = $website;
        $this->initialisationFromShop($website->shop, $request);


        return $this->handle(parent: $this->parent, bucket: $this->bucket);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function infoInFulfilment(Organisation $organisation, Fulfilment $fulfilment, Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'info';
        $this->scope  = $fulfilment;
        $this->parent = $website;
        $this->initialisationFromFulfilment($fulfilment, $request);


        return $this->handle(parent: $this->parent, bucket: $this->bucket);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function blog(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'blog';
        $this->scope  = $shop;
        $this->parent = $website;
        $this->initialisationFromShop($website->shop, $request);


        return $this->handle(parent: $this->parent, bucket: $this->bucket);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function operations(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'operations';
        $this->scope  = $shop;
        $this->parent = $website;
        $this->initialisationFromShop($website->shop, $request);


        return $this->handle(parent: $this->parent, bucket: $this->bucket);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function operationsInFulfilment(Organisation $organisation, Fulfilment $fulfilment, Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'operations';
        $this->scope  = $fulfilment;
        $this->parent = $website;
        $this->initialisationFromFulfilment($fulfilment, $request);


        return $this->handle(parent: $this->parent, bucket: $this->bucket);
    }


    protected function getElementGroups(Organisation|Website|Webpage $parent): array
    {
        return [
            'state' => [
                'label'    => __('State'),
                'elements' => array_merge_recursive(
                    WebpageStateEnum::labels(),
                    WebpageStateEnum::count($parent)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('webpages.state', $elements);
                }

            ],

        ];
    }


    public function handle(Group|Organisation|Website|Webpage $parent, $prefix = null, $bucket = null): LengthAwarePaginator
    {
        if ($bucket) {
            $this->bucket = $bucket;
        }

        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('webpages.code', $value)
                    ->orWhereStartWith('webpages.url', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Webpage::class);

        if (!($parent instanceof Group)) {
            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                $queryBuilder->whereElementGroup(
                    key: $key,
                    allowedElements: array_keys($elementGroup['elements']),
                    engine: $elementGroup['engine'],
                    prefix: $prefix
                );
            }
        }


        if ($parent instanceof Organisation) {
            $queryBuilder->where('webpages.organisation_id', $parent->id);
        } elseif ($parent instanceof Webpage) {
            $queryBuilder->where('webpages.parent_id', $parent->id);
        } elseif ($parent instanceof Group) {
            $queryBuilder->where('webpages.group_id', $parent->id);
        } else {
            $queryBuilder->where('webpages.website_id', $parent->id);
        }

        if ($bucket == 'catalogue') {
            $queryBuilder->where('webpages.type', WebpageTypeEnum::CATALOGUE);
        } elseif ($bucket == 'content') {
            $queryBuilder->where('webpages.type', WebpageTypeEnum::CONTENT);
        } elseif ($bucket == 'info') {
            $queryBuilder->where('webpages.type', WebpageTypeEnum::INFO);
        } elseif ($bucket == 'operations') {
            $queryBuilder->where('webpages.type', WebpageTypeEnum::OPERATIONS);
        } elseif ($bucket == 'blog') {
            $queryBuilder->where('webpages.type', WebpageTypeEnum::BLOG);
        } elseif ($bucket == 'storefront') {
            $queryBuilder->where('webpages.type', WebpageTypeEnum::STOREFRONT);
        }

        $queryBuilder->leftJoin('organisations', 'webpages.organisation_id', '=', 'organisations.id');
        $queryBuilder->leftJoin('shops', 'webpages.shop_id', '=', 'shops.id');
        $queryBuilder->leftJoin('websites', 'webpages.website_id', '=', 'websites.id');

        return $queryBuilder
            ->defaultSort('webpages.level')
            ->select([
                'webpages.code',
                'webpages.id',
                'webpages.type',
                'webpages.slug',
                'webpages.level',
                'webpages.sub_type',
                'webpages.url',
                'organisations.slug as organisation_slug',
                'shops.slug as shop_slug',
                'shops.name as shop_name',
                'organisations.name as organisation_name',
                'websites.domain as website_url',
                'websites.slug as website_slug'
            ])
            ->allowedSorts(['code', 'type', 'level', 'url'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(Group|Organisation|Website|Webpage $parent, ?array $modelOperations = null, $prefix = null, string $bucket = ''): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix, $bucket) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            if (!($parent instanceof Group)) {
                foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                    $table->elementGroup(
                        key: $key,
                        label: $elementGroup['label'],
                        elements: $elementGroup['elements']
                    );
                }
            }


            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Organisation' => [
                            'title'       => __("No webpages found"),
                            'description' => $parent->webStats->number_websites == 0 ? __('Nor any website exist 🤭') : null,
                            'count'       => $parent->webStats->number_webpages,

                        ],
                        'Website', 'Group' => [
                            'title' => __("No webpages found"),
                            'count' => $parent->webStats->number_webpages,
                        ],
                        default => null
                    }
                )
                ->column(key: 'level', label: '', icon: 'fal fa-sort-amount-down-alt', tooltip: __('Level'), canBeHidden: false, sortable: true, type: 'icon');
            if ($bucket == 'all') {
                $table->column(key: 'type', label: '', icon: 'fal fa-shapes', tooltip: __('Type'), canBeHidden: false, type: 'icon');
            }
            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'url', label: __('url'), canBeHidden: false, sortable: true, searchable: true);
            if ($parent instanceof Group) {
                $table->column(key: 'organisation_name', label: __('organisation'), canBeHidden: false, sortable: true, searchable: true)
                    ->column(key: 'shop_name', label: __('shop'), canBeHidden: false, sortable: true, searchable: true);
            }
            $table->defaultSort('level');
        };
    }

    public function jsonResponse(LengthAwarePaginator $webpages): AnonymousResourceCollection
    {
        return WebpagesResource::collection($webpages);
    }

    public function htmlResponse(LengthAwarePaginator $webpages, ActionRequest $request): Response
    {
        $subNavigation = [];


        if ($this->parent instanceof Website) {
            $subNavigation = $this->getWebpageNavigation($this->parent);
        }

        return Inertia::render(
            'Org/Web/Webpages',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('webpages'),
                'pageHead'    => [
                    'title'         => __('webpages'),
                    'icon'          => [
                        'icon'  => ['fal', 'fa-browser'],
                        'title' => __('webpage')
                    ],
                    'subNavigation' => $subNavigation,
                    'actions'       => [
                        [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('webpage'),
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.web.webpages.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                        ]
                    ]
                ],
                'data'        => WebpagesResource::collection($webpages),

            ]
        )->table($this->tableStructure(parent: $this->parent, bucket: $this->bucket));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters, ?string $suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Webpages'),
                        'icon'  => 'fal fa-bars'
                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        return match ($routeName) {
            'grp.web.webpages.index' =>
            array_merge(
                ShowGroupDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'grp.web.webpages.index',
                        null
                    ],
                    $suffix
                ),
            ),


            'grp.org.shops.show.web.webpages.index' =>
            array_merge(
                ShowWebsite::make()->getBreadcrumbs(
                    'Shop',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.web.webpages.index',
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),
            'grp.org.shops.show.web.webpages.index.type.catalogue' =>
            array_merge(
                ShowWebsite::make()->getBreadcrumbs(
                    'Shop',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.web.webpages.index.type.catalogue',
                        'parameters' => $routeParameters
                    ],
                    trim('('.__('Shop').') '.$suffix)
                )
            ),
            'grp.org.shops.show.web.webpages.index.type.content' =>
            array_merge(
                ShowWebsite::make()->getBreadcrumbs(
                    'Shop',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.web.webpages.index.type.content',
                        'parameters' => $routeParameters
                    ],
                    trim('('.__('Content').') '.$suffix)
                )
            ),
            'grp.org.shops.show.web.webpages.index.type.small-print' =>
            array_merge(
                ShowWebsite::make()->getBreadcrumbs(
                    'Shop',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.web.webpages.index.type.small-print',
                        'parameters' => $routeParameters
                    ],
                    trim('('.__('Small Print').') '.$suffix)
                )
            ),
            'grp.org.shops.show.web.webpages.index.type.checkout' =>
            array_merge(
                ShowWebsite::make()->getBreadcrumbs(
                    'Shop',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.web.webpages.index.type.checkout',
                        'parameters' => $routeParameters
                    ],
                    trim('('.__('Checkout').') '.$suffix)
                )
            ),
            'grp.overview.web.webpages.index' =>
            array_merge(
                ShowGroupOverviewHub::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),
            'grp.org.fulfilments.show.web.webpages.index',
            'grp.org.fulfilments.show.web.webpages.index.type.content',
            'grp.org.fulfilments.show.web.webpages.index.type.info',
            'grp.org.fulfilments.show.web.webpages.index.type.operations'
            =>
            array_merge(
                ShowWebsite::make()->getBreadcrumbs(
                    'Fulfilment',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.fulfilments.show.web.webpages.index',
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),

            default => []
        };
    }
}
