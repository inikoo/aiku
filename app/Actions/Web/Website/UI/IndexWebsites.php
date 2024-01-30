<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 29 May 2023 12:18:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Enums\Web\Website\WebsiteStateEnum;
use App\Http\Resources\Web\WebsiteResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
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

class IndexWebsites extends OrgAction
{
    private Organisation|Fulfilment|Shop $parent;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Organisation) {
            $this->canEdit = $request->user()->hasPermissionTo("shops.{$this->organisation->id}.edit");

            return $request->user()->hasPermissionTo("shops.{$this->organisation->id}.view");
        } elseif ($this->parent instanceof Fulfilment) {
            $this->canEdit = $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.edit");

            return $request->user()->hasPermissionTo("fulfilment.{$this->fulfilment->id}.view");
        } elseif ($this->parent instanceof Shop) {
            $this->canEdit = $request->user()->hasPermissionTo("web.{$this->shop->id}.edit");

            return $request->user()->hasPermissionTo("web.{$this->shop->id}.view");
        }


        return false;
    }

    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment);
    }

    protected function getElementGroups(Group|Organisation|Shop|Fulfilment $parent): array
    {
        return
            [
                'state' => [
                    'label'    => __('State'),
                    'elements' => array_merge_recursive(
                        WebsiteStateEnum::labels(),
                        WebsiteStateEnum::count($parent)
                    ),

                    'engine' => function ($query, $elements) {
                        $query->whereIn('websites.state', $elements);
                    }

                ]
            ];
    }


    public function handle(Group|Organisation|Shop|Fulfilment $parent, $prefix = null): LengthAwarePaginator
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

        if ($parent instanceof Organisation) {
            $queryBuilder->where('websites.organisation_id', $parent->id);
        } elseif ($parent instanceof Fulfilment) {
            $queryBuilder->where('websites.shop_id', $parent->shop->id);
        } elseif ($parent instanceof Shop) {
            $queryBuilder->where('websites.shop_id', $parent->id);
        } elseif ($parent instanceof Group) {
            $queryBuilder->where('websites.group_id', $parent->id);
        }


        foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        return $queryBuilder
            ->defaultSort('websites.code')
            ->select(['websites.code', 'websites.name', 'websites.slug', 'websites.domain', 'status', 'websites.state'])
            ->allowedSorts(['slug', 'code', 'name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Group|Organisation|Shop|Fulfilment $parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix, $parent) {
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



            $countWebsites = match (class_basename($parent)) {
                'Group','Organisation'=> $parent->webStats->number_websites,
                'Shop'  => $parent->website()->count(),
                default => $parent->shop->website()->count(),
            };

            $table
                ->withModelOperations($modelOperations)
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title' => __('No websites found'),
                        'count' => $countWebsites

                    ]
                )
                ->column(key: 'state', label: ['fal', 'fa-yin-yang'], sortable: true)
                ->column(key: 'slug', label: __('code'), sortable: true)
                ->column(key: 'name', label: __('name'), sortable: true)
                ->column(key: 'domain', label: __('domain'), sortable: true)
                ->defaultSort('slug');
        };
    }

    public function jsonResponse(LengthAwarePaginator $websites): AnonymousResourceCollection
    {
        return WebsiteResource::collection($websites);
    }

    public function htmlResponse(LengthAwarePaginator $websites, ActionRequest $request): Response
    {
        $scope     = $this->parent;
        $container = null;
        $title     =__('Websites');
        if (class_basename($scope) == 'Shop') {
            $title     =__("Shop Websites");
            $container = [
                'icon'    => ['fal', 'fa-store-alt'],
                'tooltip' => __('Shop'),
                'label'   => Str::possessive($scope->name)
            ];
        } elseif (class_basename($scope) == 'Fulfilment') {
            $title     =__("Fulfilment Shop Websites");
            $container = [
                'icon'    => ['fal', 'fa-pallet'],
                'tooltip' => __('Fulfilment shop'),
                'label'   => Str::possessive($scope->shop->name)
            ];
        }


        return Inertia::render(
            'Org/Web/Websites',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => $title,

                'pageHead'    => [
                    'title'       => __('websites'),
                    'container'   => $container,
                    'icon'        => [
                        'title' => __('website'),
                        'icon'  => 'fal fa-globe'
                    ],

                    /*
                    'actions' => [
                        $this->canEdit ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('Create a no shop connected website'),
                            'label'   => __('new static website'),
                            'route'   => [
                                'name' => 'grp.org.shops.show.websites.create',
                            ]
                        ] : false,


                ]
                    */
                ],
                'data'        => WebsiteResource::collection($websites),

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
                        'label' => __('websites'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };



        return match ($routeName) {
            'grp.org.fulfilments.show.websites.index'=>
            array_merge(
                ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'      => $routeName,
                        'parameters'=> $routeParameters
                    ]
                ),
            ),


            'grp.org.shops.show.websites.index' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'      => 'grp.org.shops.show',
                        'parameters'=> $routeParameters
                    ]
                ),
            ),

            default => []
        };
    }

}
