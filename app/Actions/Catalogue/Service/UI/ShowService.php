<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:05:53 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\Service\UI;

use App\Actions\Catalogue\Shop\UI\IndexShops;
use App\Actions\Catalogue\Shop\UI\ShowCatalogue;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentAssets;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\OrgAction;
use App\Enums\UI\Catalogue\ProductTabsEnum;
use App\Enums\UI\Catalogue\ServiceTabsEnum;
use App\Enums\UI\Fulfilment\FulfilmentServiceTabsEnum;
use App\Http\Resources\Catalogue\ProductsResource;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Service;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowService extends OrgAction
{
    private Organisation|Shop|Fulfilment|ProductCategory $parent;

    public function handle(Service $service): Service
    {
        return $service;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->parent instanceof Fulfilment) {
            $this->canEdit   = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
            $this->canDelete = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
        } elseif($this->parent instanceof Organisation) {
            $this->canEdit   = $request->user()->hasPermissionTo("shops.{$this->organisation->id}.edit");
            $this->canDelete = $request->user()->hasPermissionTo("shops.{$this->organisation->id}.edit");
            return $request->user()->hasPermissionTo("shops.{$this->organisation->id}.view");
        } else {
            $this->canEdit   = $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");
            $this->canDelete = $request->user()->hasPermissionTo("products.{$this->shop->id}.edit");
            return $request->user()->hasPermissionTo("products.{$this->shop->id}.view");
        }


    }

    public function inOrganisation(Organisation $organisation, Service $service, ActionRequest $request): Service
    {
        $this->parent= $organisation;
        $this->initialisation($organisation, $request)->withTab(FulfilmentServiceTabsEnum::values());
        return $this->handle($service);
    }

    public function asController(Organisation $organisation, Shop $shop, Service $service, ActionRequest $request): Service
    {
        $this->parent= $shop;
        $this->initialisationFromShop($shop, $request)->withTab(FulfilmentServiceTabsEnum::values());
        return $this->handle($service);
    }

    public function inDepartment(Organisation $organisation, Shop $shop, ProductCategory $department, Service $service, ActionRequest $request): Service
    {
        $this->parent= $department;
        $this->initialisationFromShop($shop, $request)->withTab(FulfilmentServiceTabsEnum::values());

        return $this->handle($service);
    }

    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Service $service, ActionRequest $request): Service
    {
        $this->parent= $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(ProductTabsEnum::values());
        return $this->handle($service);
    }

    public function htmlResponse(Service $service, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Fulfilment/Service',
            [
                'title'       => __('service'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($service, $request),
                    'next'     => $this->getNext($service, $request),
                ],
                'pageHead'    => [
                    'model'   => __('service'),
                    'title'   => $service->code,
                    'icon'    =>
                        [
                            'icon'  => ['fal', 'fa-concierge-bell'],
                            'title' => __('service')
                        ],
                    'actions' => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'edit',
                            'route' => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false,
                        $this->canDelete ? [
                            'type'  => 'button',
                            'style' => 'delete',
                            'route' => [
                                'name'       => 'shops.show.products.remove',
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ] : false
                    ]
                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => FulfilmentServiceTabsEnum::navigation()
                ],


                FulfilmentServiceTabsEnum::SHOWCASE->value => $this->tab == ServiceTabsEnum::SHOWCASE->value ?
                    fn () => GetServiceShowcase::run($service)
                    : Inertia::lazy(fn () => GetServiceShowcase::run($service)),
            ]
        );
    }

    public function jsonResponse(Asset $product): ProductsResource
    {
        return new ProductsResource($product);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (Service $service, array $routeParameters, $suffix) {
            return [

                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('Services')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $service->slug,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],

            ];
        };

        $service=Service::where('slug', $routeParameters['service'])->first();

        return match ($routeName) {
            'shops.products.show' =>
            array_merge(
                IndexShops::make()->getBreadcrumbs('grp.org.shops.index', $routeParameters['organisation']),
                $headCrumb(
                    $routeParameters['product'],
                    [
                        'index' => [
                            'name'       => 'shops.products.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'shops.products.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.shops.show.catalogue.products.show' =>
            array_merge(
                ShowCatalogue::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $service,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.catalogue.products.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.catalogue.products.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.fulfilments.show.billables.show' =>
            array_merge(
                ShowFulfilment::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $service,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.billables.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.billables.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.fulfilments.show.billables.services.show' =>
            array_merge(
                (new IndexFulfilmentAssets())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $service,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.billables.services.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.billables.services.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(Service $service, ActionRequest $request): ?array
    {
        $previous = Service::where('slug', '<', $service->slug)->orderBy('slug', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Service $service, ActionRequest $request): ?array
    {
        $next = Service::where('slug', '>', $service->slug)->orderBy('slug')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Service $service, string $routeName): ?array
    {
        if (!$service) {
            return null;
        }

        return match ($routeName) {
            'grp.org.fulfilments.show.billables.services.show' => [
                'label' => $service->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $service->organisation->slug,
                        'fulfilment'   => $service->asset->shop->slug,
                        'service'      => $service->slug
                    ],
                ],
            ],
            default => null,
        };
    }
}
