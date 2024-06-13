<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:05:53 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\Product\UI;

use App\Actions\Catalogue\Shop\UI\IndexShops;
use App\Actions\Catalogue\Shop\UI\ShowCatalogue;
use App\Actions\CRM\Customer\UI\IndexCustomers;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentAssets;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Mail\Mailshot\UI\IndexMailshots;
use App\Actions\Ordering\Order\UI\IndexOrders;
use App\Actions\OrgAction;
use App\Enums\UI\Catalogue\ProductTabsEnum;
use App\Enums\UI\Catalogue\ServiceTabsEnum;
use App\Enums\UI\Fulfilment\FulfilmentAssetTabsEnum;
use App\Http\Resources\Catalogue\ProductsResource;
use App\Http\Resources\CRM\CustomersResource;
use App\Http\Resources\Mail\MailshotResource;
use App\Http\Resources\Sales\OrderResource;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowPhysicalGoods extends OrgAction
{
    private Organisation|Shop|Fulfilment|ProductCategory $parent;

    public function handle(Asset $asset): Asset
    {
        return $asset;
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

    public function inOrganisation(Organisation $organisation, Asset $asset, ActionRequest $request): Asset
    {
        $this->parent= $organisation;
        $this->initialisation($organisation, $request)->withTab(FulfilmentAssetTabsEnum::values());
        return $this->handle($asset);
    }

    public function asController(Organisation $organisation, Shop $shop, Asset $asset, ActionRequest $request): Asset
    {
        $this->parent= $shop;
        $this->initialisationFromShop($shop, $request)->withTab(FulfilmentAssetTabsEnum::values());
        return $this->handle($asset);
    }

    public function inDepartment(Organisation $organisation, Shop $shop, ProductCategory $department, Asset $asset, ActionRequest $request): Asset
    {
        $this->parent= $department;
        $this->initialisationFromShop($shop, $request)->withTab(FulfilmentAssetTabsEnum::values());

        return $this->handle($asset);
    }

    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Asset $asset, ActionRequest $request): Asset
    {
        $this->parent= $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(FulfilmentAssetTabsEnum::values());
        return $this->handle($asset);
    }

    public function htmlResponse(Asset $asset, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Fulfilment/PhysicalGood',
            [
                'title'       => __('asset'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($asset, $request),
                    'next'     => $this->getNext($asset, $request),
                ],
                'pageHead'    => [
                    'model'   => __('asset'),
                    'title'   => $asset->code,
                    'icon'    =>
                        [
                            'icon'  => ['fal', 'fa-cube'],
                            'title' => __('asset')
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
                    'navigation' => FulfilmentAssetTabsEnum::navigation()
                ],


                FulfilmentAssetTabsEnum::SHOWCASE->value => $this->tab == FulfilmentAssetTabsEnum::SHOWCASE->value ?
                    fn () => GetPhysicalGoodShowcase::run($asset)
                    : Inertia::lazy(fn () => GetPhysicalGoodShowcase::run($asset)),

            ]
        );
    }

    public function jsonResponse(Asset $asset): ProductsResource
    {
        return new ProductsResource($asset);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (Asset $asset, array $routeParameters, $suffix) {
            return [

                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('assets')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $asset->slug,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],

            ];
        };

        $asset = Asset::where('slug', $routeParameters['asset'])->first();

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
                    $asset,
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
            'grp.org.fulfilments.show.assets.outers.show' =>
            array_merge(
                (new IndexFulfilmentAssets())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $asset,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.assets.outers.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.assets.outers.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(Asset $asset, ActionRequest $request): ?array
    {
        $previous = Asset::where('slug', '<', $asset->slug)->orderBy('slug', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Asset $asset, ActionRequest $request): ?array
    {
        $next = Asset::where('slug', '>', $asset->slug)->orderBy('slug')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Asset $asset, string $routeName): ?array
    {
        if (!$asset) {
            return null;
        }

        return match ($routeName) {
            'shops.products.show' => [
                'label' => $asset->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'product' => $asset->slug,
                    ],
                ],
            ],
            'grp.org.shops.show.catalogue.products.show' => [
                'label' => $asset->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $this->parent->slug,
                        'shop'         => $asset->shop->slug,
                        'product'      => $asset->slug,
                    ],
                ],
            ],
            'grp.org.fulfilments.show.assets.show' => [
                'label' => $asset->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation' => $this->parent->slug,
                        'fulfilment'   => $asset->shop->slug,
                        'asset'      => $asset->slug,
                    ],
                ],
            ],
            default => null,
        };
    }
}
