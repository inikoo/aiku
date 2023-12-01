<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 29 May 2023 12:18:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Http\Resources\Market\ShopResource;
use App\Http\Resources\Web\WebsiteResource;
use App\InertiaTable\InertiaTable;
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
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('websites.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('websites.view')
            );
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle();
    }

    protected function getElementGroups(): void
    {
        $this->elementGroups =
            [
                'state' => [
                    'label'    => __('State'),
                    'elements' => array_merge_recursive(
                        WebsiteStateEnum::labels(),
                        WebsiteStateEnum::count()
                    ),

                    'engine' => function ($query, $elements) {
                        $query->whereIn('websites.state', $elements);
                    }

                ]
            ];
    }

    /** @noinspection PhpUndefinedMethodInspection */
    public function handle($prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereAnyWordStartWith('websites.name', $value)
                    ->orWhere('websites.domain', 'ilike', "%$value%")
                    ->orWhere('websites.code', 'ilike', "$value%");
            });
        });
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Website::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }


        return $queryBuilder
            ->defaultSort('websites.code')
            ->select(['websites.code', 'websites.name', 'websites.slug', 'websites.domain', 'in_maintenance', 'websites.state'])
            ->allowedSorts(['slug', 'code', 'name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            foreach ($this->elementGroups as $key => $elementGroup) {
                $table->elementGroup(
                    key: $key,
                    label: $elementGroup['label'],
                    elements: $elementGroup['elements']
                );
            }

            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title' => __('No websites found'),
                        'count' => app('currentTenant')->webStats->number_websites,

                    ]
                )
                ->column(key: 'state', label: ['fal', 'fa-yin-yang'], sortable: true)
                ->column(key: 'slug', label: __('code'), sortable: true)
                ->column(key: 'name', label: __('name'), sortable: true)
                ->column(key: 'domain', label: __('domain'), sortable: true)
                ->defaultSort('slug');
        };
    }

    public function jsonResponse(): AnonymousResourceCollection
    {
        return ShopResource::collection($this->handle());
    }

    public function htmlResponse(LengthAwarePaginator $websites, ActionRequest $request): Response
    {
        return Inertia::render(
            'Web/Websites',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('websites'),
                'pageHead'    => [
                    'title'   => __('websites'),
                    'icon'    => [
                        'title' => __('website'),
                        'icon'  => 'fal fa-globe'
                    ],
                    'actions' => [
                        $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('Create a no shop connected website'),
                            'label'   => __('new static website'),
                            'route'   => [
                                'name' => 'grp.web.websites.create',
                            ]
                        ] : false,


                    ]
                ],
                'data'        => WebsiteResource::collection($websites),

            ]
        )->table($this->tableStructure());
    }

    /** @noinspection PhpUnusedParameterInspection */
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
            'grp.web.websites.index' =>
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => 'grp.web.websites.index',
                        null
                    ]
                ),
            ),

            default => []
        };
    }
}
