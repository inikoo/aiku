<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 01 Feb 2024 14:48:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage\UI;

use App\Actions\OrgAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Actions\Web\HasWebAuthorisation;
use App\Actions\Web\Website\UI\ShowWebsite;
use App\Enums\Web\Webpage\WebpageStateEnum;
use App\Http\Resources\Web\WebpageResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexWebpages extends OrgAction
{
    use HasWebAuthorisation;

    private Organisation|Website|Fulfilment|Webpage $parent;


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
        $this->scope  = $fulfilment;
        $this->parent = $website;
        $this->initialisationFromFulfilment($fulfilment, $request);


        return $this->handle($this->parent);
    }

    public function asController(Organisation $organisation, Shop $shop, Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->scope  = $shop;
        $this->parent = $website;
        $this->initialisationFromShop($website->shop, $request);


        return $this->handle($this->parent);
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
                    $query->whereIn('state', $elements);
                }

            ],

        ];
    }


    public function handle(Organisation|Website|Webpage $parent, $prefix = null): LengthAwarePaginator
    {
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

        foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }


        if ($parent instanceof Organisation) {
            $queryBuilder->where('webpages.organisation_id', $parent->id);
        } elseif ($parent instanceof Webpage) {
            $queryBuilder->where('webpages.parent_id', $parent->id);
        } else {
            $queryBuilder->where('webpages.website_id', $parent->id);
        }


        return $queryBuilder
            ->defaultSort('webpages.level')
            ->select(['code', 'id', 'type', 'slug', 'level', 'purpose', 'url'])
            ->allowedSorts(['code', 'type', 'level', 'url'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation|Website|Webpage $parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements']
                );
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
                        'Website' => [
                            'title' => __("No webpages found"),
                            'count' => $parent->webStats->number_webpages,
                        ],
                        default => null
                    }
                )
                ->column(key: 'level', label: ['fal', 'fa-sort-amount-down-alt'], canBeHidden: false, sortable: true, type: 'icon')
                ->column(key: 'type', label: ['fal', 'fa-shapes'], canBeHidden: false, type: 'icon')
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'url', label: __('url'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('level');
        };
    }

    public function jsonResponse(LengthAwarePaginator $webpages): AnonymousResourceCollection
    {
        return WebpageResource::collection($webpages);
    }

    public function htmlResponse(LengthAwarePaginator $webpages, ActionRequest $request): Response
    {
        $scope     = $this->parent;
        $container = null;
        if (class_basename($scope) == 'Website') {
            $container = [
                'icon'    => ['fal', 'fa-globe'],
                'tooltip' => __('Website'),
                'label'   => Str::possessive($scope->code)
            ];
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
                    'title'     => __('webpages'),
                    'container' => $container,
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-browser'],
                        'title' => __('webpage')
                    ]
                ],
                'data'        => WebpageResource::collection($webpages),

            ]
        )->table($this->tableStructure($this->parent));
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Webpages'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.web.webpages.index' =>
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'grp.web.webpages.index',
                        null
                    ]
                ),
            ),


            'grp.org.shops.show.web.websites.show.webpages.index' =>
            array_merge(
                ShowWebsite::make()->getBreadcrumbs(
                    'grp.org.shops.show.web.websites.show',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.web.websites.show.webpages.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            'grp.org.fulfilments.show.web.websites.show.webpages.index' =>
            array_merge(
                ShowWebsite::make()->getBreadcrumbs(
                    'grp.org.fulfilments.show.web.websites.show',
                    $routeParameters
                ),
                $headCrumb(
                    [
                        'name'       => 'grp.org.fulfilments.show.web.websites.show.webpages.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}
