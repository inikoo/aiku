<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\Collection\UI;

use App\Actions\Catalogue\Shop\UI\ShowCatalogue;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HaCatalogueAuthorisation;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Http\Resources\Catalogue\CollectionResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Collection;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class IndexCollection extends OrgAction
{
    use HaCatalogueAuthorisation;

    private Shop|Organisation $parent;

    protected function getElementGroups(Shop|Organisation $parent): array
    {
        return [
            'type'  => [
                'label'    => __('Type'),
                'elements' => array_merge_recursive(
                    AssetTypeEnum::labels($parent),
                    AssetTypeEnum::count($parent)
                ),

                'engine' => function ($query, $elements) {
                    $query->whereIn('type', $elements);
                }
            ],
        ];
    }

    public function handle(Shop|Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(Collection::class);

        foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }

        $queryBuilder
            ->defaultSort('collections.code')
            ->select([
                'collections.code',
                'collections.name',
                'collections.created_at',
                'collections.updated_at',
                'collections.slug',
            ]);

        if (class_basename($parent) == 'Shop') {
            $queryBuilder->where('collections.shop_id', $parent->id);
            $queryBuilder->leftJoin('shops', 'collections.shop_id', 'shops.id');
            $queryBuilder->addSelect(
                'shops.slug as shop_slug',
                'shops.code as shop_code',
                'shops.name as shop_name',
            );
        } elseif (class_basename($parent) == 'Organisation') {
            $queryBuilder->where('collections.organisation_id', $parent->id);

        } else {
            abort(419);
        }


        return $queryBuilder
            ->allowedSorts(['code', 'name'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(
        Shop|Organisation $parent,
        ?array $modelOperations = null,
        $prefix = null,
        $canEdit = false
    ): Closure {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix, $canEdit) {
            if ($prefix) {
                $table->name($prefix)->pageName($prefix . 'Page');
            }

            // foreach ($this->getElementGroups($parent) as $key => $elementGroup) {
            //     $table->elementGroup(
            //         key: $key,
            //         label: $elementGroup['label'],
            //         elements: $elementGroup['elements']
            //     );
            // }

            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    match (class_basename($parent)) {
                        'Organisation' => [
                            'title'       => __("No departments found"),
                            'description' => $canEdit && $parent->catalogueStats->number_shops == 0 ? __('Get started by creating a shop. ✨') : '',
                            'count'       => $parent->catalogueStats->number_departments,
                            'action'      => $canEdit && $parent->catalogueStats->number_shops == 0 ?
                                [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new shop'),
                                'label'   => __('shop'),
                                'route'   => [
                                    'name'       => 'grp.org.shops.create',
                                    'parameters' => [$parent->slug]
                                ]
                            ] : null

                        ],
                        'Shop' => [
                            'title'       => __("No collections found"),
                            'description' => __('Get started by creating a new collection. ✨')
                               ,
                            'count'       => $parent->stats->number_collections,
                            'action'      => [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new collection'),
                                'label'   => __('collection'),
                                'route'   => [
                                    'name'           => 'grp.org.shops.show.catalogue.collections.create', //creating
                                        'parameters' => [$parent->organisation->slug,$parent->slug]
                                ]
                            ]
                        ],
                        default => null
                    }
                );

            $table
                ->column(key: 'code', label: __('Code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('Name'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function jsonResponse(LengthAwarePaginator $collections): AnonymousResourceCollection
    {
        return CollectionResource::collection($collections);
    }

    public function htmlResponse(LengthAwarePaginator $collections, ActionRequest $request): Response
    {

        $scope     = $this->parent;
        $container = null;
        if (class_basename($scope) == 'Shop') {
            $container = [
                'icon'    => ['fal', 'fa-store-alt'],
                'tooltip' => __('Shop'),
                'label'   => Str::possessive($scope->name)
            ];
        }
        return Inertia::render(
            'Org/Catalogue/Collections',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'    => __('Collections'),
                'pageHead' => [
                    'title'     => __('Collections'),
                    'container' => $container,
                    'icon'      => [
                        'icon'  => ['fal', 'fa-cube'],
                        'title' => __('Collections')
                    ],
                    'actions'   => [
                        $this->canEdit && $request->route()->getName() == 'grp.org.shops.show.catalogue.collections.index' ? [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new collection'),
                            'label'   => __('collection'),
                            'route'   => [
                                'name'       => 'grp.org.shops.show.catalogue.collections.create',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,
                    ]
                ],
                'data' => CollectionResource::collection($collections),
            ]
        )->table($this->tableStructure($this->parent));
    }

    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);
        return $this->handle(parent: $organisation);
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);
        return $this->handle(parent: $shop);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = null): array
    {
        $headCrumb = function (array $routeParameters, ?string $suffix) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Collections'),
                        'icon'  => 'fal fa-cube'
                    ],
                    'suffix' => $suffix
                ]
            ];
        };
        return match ($routeName) {
            'grp.org.shops.show.catalogue.collections.index' =>
            array_merge(
                ShowCatalogue::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ],
                    $suffix
                )
            ),

            default => []
        };
    }
}
