<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 15:31:00 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\Helpers\History\IndexHistories;
use App\Actions\InertiaAction;
use App\Actions\Inventory\Location\UI\IndexLocations;
use App\Actions\Inventory\WarehouseArea\UI\IndexWarehouseAreas;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Enums\UI\WarehouseTabsEnum;
use App\Enums\UI\WebsiteWorkshopTabsEnum;
use App\Http\Resources\Inventory\LocationResource;
use App\Http\Resources\Inventory\WarehouseAreaResource;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Http\Resources\SysAdmin\HistoryResource;
use App\Models\Inventory\Warehouse;
use App\Models\Web\Website;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property \App\Models\Web\Website $website
 */
class WorkshopWebsite extends InertiaAction
{
    private ActionRequest $request;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->can('websites.edit');
        $this->canDelete = $request->user()->can('websites.edit');
        return $request->user()->hasPermissionTo("websites.view");
    }

    public function asController(Website $website, ActionRequest $request): void
    {
        $this->initialisation($request)->withTab(WebsiteWorkshopTabsEnum::values());
        $this->website   = $website;
        $this->request   = $request;
    }


    public function htmlResponse(): Response
    {
        return Inertia::render(
            'Web/WebsiteWorkshop',
            [
                'title'                            => __('workshop'),
                'breadcrumbs'                      => $this->getBreadcrumbs($this->website),
                'navigation'                       => [
                    'previous' => $this->getPrevious($this->website, $this->request),
                    'next'     => $this->getNext($this->website, $this->request),
                ],
                'pageHead'                         => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'website'],
                            'title' => __('website')
                        ],
                    'title'   => $this->website->name,
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : false,
                        $this->canDelete ? [
                            'type'  => 'button',
                            'style' => 'delete',
                            'route' => [
                                'name'       => 'inventory.websites.remove',
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ] : false
                    ],
                    'meta' => [
                        [
                            'name'     => trans_choice('website area|website areas', $this->website->stats->number_website_areas),
                            'number'   => $this->website->stats->number_website_areas,
                            'href'     => [
                                'inventory.websites.show.website-areas.index',
                                $this->website->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-map-signs',
                                'tooltip' => __('website areas')
                            ]
                        ],
                        [
                            'name'     => trans_choice('location|locations', $this->website->stats->number_locations),
                            'number'   => $this->website->stats->number_locations,
                            'href'     => [
                                'inventory.websites.show.locations.index',
                                $this->website->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-inventory',
                                'tooltip' => __('locations')
                            ]
                        ]
                    ]

                ],
                'tabs'                                   => [

                    'current'    => $this->tab,
                    'navigation' => WebsiteWorkshopTabsEnum::navigation(),


                ],


                WebsiteWorkshopTabsEnum::HEADER->value       => $this->tab == WebsiteWorkshopTabsEnum::HEADER->value ?
                    fn () => LocationResource::collection(
                        IndexLocations::run(
                            parent: $this->website,
                            prefix: 'locations'
                        )
                    )
                    : Inertia::lazy(fn () => LocationResource::collection(
                        IndexLocations::run(
                            parent: $this->website,
                            prefix: 'locations'
                        )
                    )),
                WarehouseTabsEnum::Menu->value => $this->tab == WarehouseTabsEnum::WAREHOUSE_AREAS->value
                    ?
                    fn () => WarehouseAreaResource::collection(
                        IndexWarehouseAreas::run(
                            parent: $this->website,
                            prefix: 'website_areas'
                        )
                    )
                    : Inertia::lazy(fn () => WarehouseAreaResource::collection(
                        IndexWarehouseAreas::run(
                            parent: $this->website,
                            prefix: 'website_areas'
                        )
                    )),

                WarehouseTabsEnum::HISTORY->value => $this->tab == WarehouseTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistories::run($this->website))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistories::run($this->website)))

            ]
        )->table(IndexLocations::make()->tableStructure(
            //            modelOperations: [
            //                'createLink' => $this->canEdit ? [
            //                    'route' => [
            //                        'name'       => 'inventory.websites.show.locations.create',
            //                        'parameters' => array_values($this->originalParameters)
            //                    ],
            //                    'label' => __('location')
            //                ] : false,
            //            ],
            //            prefix: 'locations'
        ))->table(IndexWarehouseAreas::make()->tableStructure(
            //            modelOperations: [
            //                'createLink' => $this->canEdit ? [
            //                    'route' => [
            //                        'name'       => 'inventory.websites.show.website-areas.create',
            //                        'parameters' => array_values($this->originalParameters)
            //                    ],
            //                    'label' => __('area')
            //                ] : false,
            //            ],
            //            prefix: 'website_areas'
        ))->table(IndexHistories::make()->tableStructure());
    }


    public function jsonResponse(): WarehouseResource
    {
        return new WarehouseResource($this->website);
    }

    public function getBreadcrumbs(Website $website, $suffix = null): array
    {
        return array_merge(
            (new InventoryDashboard())->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'inventory.websites.index',
                            ],
                            'label' => __('website'),
                            'icon'  => 'fal fa-bars'
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'inventory.websites.show',
                                'parameters' => [$website->slug]
                            ],
                            'label' => $website->code,
                            'icon'  => 'fal fa-bars'
                        ],
                    ],
                    'suffix'         => $suffix,

                ],
            ]
        );
    }

    public function getPrevious(Warehouse $website, ActionRequest $request): ?array
    {
        $previous = Warehouse::where('code', '<', $website->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Warehouse $website, ActionRequest $request): ?array
    {
        $next = Warehouse::where('code', '>', $website->code)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Warehouse $website, string $routeName): ?array
    {
        if (!$website) {
            return null;
        }

        return match ($routeName) {
            'inventory.websites.show' => [
                'label' => $website->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'website' => $website->slug
                    ]

                ]
            ]
        };
    }
}
