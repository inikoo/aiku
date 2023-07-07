<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 07 Jul 2023 11:39:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Actions\InertiaAction;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\UI\Dashboard\Dashboard;
use App\Http\Resources\Market\ShopResource;
use App\Http\Resources\Market\WebpageResource;
use App\InertiaTable\InertiaTable;
use App\Models\Tenancy\Tenant;
use App\Models\Web\Webpage;
use App\Models\Web\Website;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexWebpages extends InertiaAction
{
    private Tenant|Website $parent;


    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('websites.view')
            );
    }

    public function inTenant(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        $this->parent = app('currentTenant');
        return $this->handle($this->parent);
    }

    public function inWebsite(Website $website, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        $this->parent = $website;
        return $this->handle($this->parent);
    }

    /** @noinspection PhpUndefinedMethodInspection */
    public function handle(Tenant|Website $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('webpages.code', 'LIKE', "%$value%")
                    ->orWhere('webpages.type', 'LIKE', "%$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Webpage::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }

        return $queryBuilder
            ->defaultSort('webpages.code')
            ->select(['code', 'id', 'type', 'slug'])
            ->allowedSorts(['code', 'type'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($parent, ?array $modelOperations = null, $prefix=null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Tenant' => [
                            'title'       => __("No webpages found"),
                            'description' => $parent->webStats->number_websites==0 ? __('Nor any website exist 🤭') : null,
                            'count'       => $parent->webStats->number_webpages,

                        ],
                        'Website' => [
                            'title'       => __("No webpages found"),
                            'count'       => $parent->stats->number_webpages,
                        ],
                        default => null
                    }
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }

    public function jsonResponse(LengthAwarePaginator $webpages): AnonymousResourceCollection
    {
        return ShopResource::collection($webpages);
    }

    public function htmlResponse(LengthAwarePaginator $webpages, ActionRequest $request): Response
    {
        $scope     = $this->parent;
        $container = null;
        if (class_basename($scope) == 'Website') {
            $container = [
                'icon'    => ['fal', 'fa-globe'],
                'tooltip' => __('Website'),
                'label'   => Str::possessive($scope->name)
            ];
        }

        return Inertia::render(
            'Web/Webpages',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
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
                        'label' => __('webpages'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'websites.webpages.index' =>
            array_merge(
                Dashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'webpages.index',
                        null
                    ]
                ),
            ),


            'shops.show.webpages.index' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($routeParameters['shop']),
                $headCrumb(
                    [
                        'name'       => 'shops.show.webpages.index',
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
