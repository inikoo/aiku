<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:38 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\Shop\UI;

use App\Actions\OrgAction;
use App\Actions\UI\WithInertia;
use App\Http\Resources\Catalogue\ShopResource;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowCatalogue extends OrgAction
{
    use AsAction;
    use WithInertia;


    public function handle(Shop $shop): Shop
    {
        return $shop;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");

        return $request->user()->hasPermissionTo("products.{$this->shop->id}.view");
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): Shop
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop);
    }

    public function htmlResponse(Shop $shop, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Catalogue/Catalogue',
            [
                'title'       => __('catalogue'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($shop, $request),
                    'next'     => $this->getNext($shop, $request),
                ],
                'pageHead'    => [
                    'title' => __('Catalogue'),
                    'model' => '',
                    'icon'  => [
                        'title' => __('Catalogue'),
                        'icon'  => 'fal fa-books'
                    ],

                ],


                'stats' => [
                    [
                        'label' => __('Department'),
                        'route' => [
                            'name'  => 'grp.profile.visit-logs.index',        // TODO
                            'parameters'    => 'zzzzzzzz'
                        ],
                        'icon'  => 'fal fa-folder-tree',
                        "color" => "#a3e635",
                        'value' => $shop->stats->number_departments,
                    ],
                    [
                        'label' => __('Families'),
                        'route' => [
                            'name'  => 'grp.profile.visit-logs.index',        // TODO
                            'parameters'    => 'zzzzzzzz'
                        ],
                        'icon'  => 'fal fa-folder',
                        "color" => "#facc15",
                        'value' => $shop->stats->number_families,
                    ],
                    [
                        'label' => __('Products'),
                        'route' => [
                            'name'  => 'grp.profile.visit-logs.index',        // TODO
                            'parameters'    => 'zzzzzzzz'
                        ],
                        'icon'  => 'fal fa-cube',
                        "color" => "#38bdf8",
                        'value' => $shop->stats->number_products,
                        'metas'  =>  [
                            [
                                "value" => "active",
                                "icon" => [
                                    "tooltip" => "active",
                                    "icon" => "fas fa-check-circle",
                                    "class" => "text-green-500"
                                ],
                                "count" => 11843,
                                "label" => "Active"
                            ],
                            [
                                "value" => "discontinuing",
                                "icon" => [
                                    "tooltip" => "discontinuing",
                                    "icon" => "fas fa-times-circle",
                                    "class" => "text-amber-500"
                                ],
                                "count" => 0,
                                "label" => "Discontinuing"
                            ],
                            [
                                "value" => "discontinued",
                                "icon" => [
                                    "tooltip" => "discontinued",
                                    "icon" => "fas fa-times-circle",
                                    "class" => "text-red-500"
                                ],
                                "count" => 29113,
                                "label" => "Discontinued"
                            ]
                        ]
                    ],
                    [
                        'label' => __('Collections'),
                        'route' => [
                            'name'  => 'grp.profile.visit-logs.index',        // TODO
                            'parameters'    => 'zzzzzzzz'
                        ],
                        'icon'  => 'fal fa-album-collection',
                        "color" => "#4f46e5",
                        'value' => $shop->stats->number_collections,
                    ],
                ]

            ]
        );
    }


    public function jsonResponse(Shop $shop): ShopResource
    {
        return new ShopResource($shop);
    }


    public function getPrevious(Shop $shop, ActionRequest $request): ?array
    {
        $previous = Shop::where('code', '<', $shop->code)->where('organisation_id', $this->organisation->id)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Shop $shop, ActionRequest $request): ?array
    {
        $next = Shop::where('code', '>', $shop->code)->where('organisation_id', $this->organisation->id)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Shop $shop, string $routeName): ?array
    {
        if (!$shop) {
            return null;
        }

        return match ($routeName) {
            'grp.org.shops.show.catalogue.dashboard' => [
                'label' => $shop->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'shop'         => $shop->slug
                    ]

                ]
            ]
        };
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.shops.show.catalogue.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Catalogue'),
                        ]
                    ]
                ]
            );
    }
}
