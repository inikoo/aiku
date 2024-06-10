<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:38 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\Shop\UI;

use App\Actions\OrgAction;
use App\Actions\UI\WithInertia;
use App\Enums\UI\Catalogue\CatalogueTabsEnum;
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
        $this->initialisationFromShop($shop, $request)->withTab(CatalogueTabsEnum::values());
        return $this->handle($shop);
    }

    public function htmlResponse(Shop $shop, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Catalogue/Shop',
            [
                'title'        => __('shop'),
                'breadcrumbs'  => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation'   => [
                    'previous' => $this->getPrevious($shop, $request),
                    'next'     => $this->getNext($shop, $request),
                ],
                'pageHead'     => [
                    'title'   => $shop->name,
                    'icon'    => [
                        'title' => __('Shop'),
                        'icon'  => 'fal fa-store-alt'
                    ],

                ],
                'tabs'         => [
                    'current'    => $this->tab,
                    'navigation' => CatalogueTabsEnum::navigation()
                ],


                /*

                CatalogueTabsEnum::DEPARTMENTS->value => $this->tab == CatalogueTabsEnum::DEPARTMENTS->value
                    ?
                    fn () => DepartmentsResource::collection(
                        IndexDepartments::run(
                            parent: $shop,
                            prefix: 'departments'
                        )
                    )
                    : Inertia::lazy(fn () => DepartmentsResource::collection(
                        IndexDepartments::run(
                            parent: $shop,
                            prefix: 'departments'
                        )
                    )),

                CatalogueTabsEnum::FAMILIES->value => $this->tab == CatalogueTabsEnum::FAMILIES->value
                    ?
                    fn () => FamiliesResource::collection(
                        IndexFamilies::run(
                            parent: $shop,
                            prefix: 'families'
                        )
                    )
                    : Inertia::lazy(fn () => FamiliesResource::collection(
                        IndexFamilies::run(
                            parent: $shop,
                            prefix: 'families'
                        )
                    )),

                CatalogueTabsEnum::PRODUCTS->value => $this->tab == CatalogueTabsEnum::PRODUCTS->value
                    ?
                    fn () => ProductsResource::collection(
                        IndexProducts::run(
                            parent: $shop,
                            prefix: 'products'
                        )
                    )
                    : Inertia::lazy(fn () => ProductsResource::collection(
                        IndexProducts::run(
                            parent: $shop,
                            prefix: 'products'
                        )
                    )),

                    CatalogueTabsEnum::COLLECTIONS->value => $this->tab == CatalogueTabsEnum::COLLECTIONS->value
                    ?
                    fn () => CollectionResource::collection(
                        IndexCollection::run(
                            parent: $shop,
                            prefix: 'collections'
                        )
                    )
                    : Inertia::lazy(fn () => CollectionResource::collection(
                        IndexCollection::run(
                            parent: $shop,
                            prefix: 'collections'
                        )
                    )),

                */

            ]
        );
        /*
        ->table(
        IndexDepartments::make()->tableStructure(
            parent: $shop,
            modelOperations: [
                'createLink' => $this->canEdit ? [
                    'route' => [
                        'name'       => 'shops.show.departments.create',
                        'parameters' => array_values([$shop->slug])
                    ],
                    'label' => __('department'),
                    'style' => 'create'
                ] : false
            ],
            prefix: 'departments'
        )
        )->table(
        IndexFamilies::make()->tableStructure(
            parent: $shop,
            modelOperations: [
                'createLink' => $this->canEdit ? [
                    'route' => [
                        'name'       => 'shops.show.families.create',
                        'parameters' => array_values([$shop->slug])
                    ],
                    'label' => __('family'),
                    'style' => 'create'
                ] : false
            ],
            prefix: 'families'
        )
        )->table(
        IndexProducts::make()->tableStructure(
            parent: $shop,
            modelOperations: [
                'createLink' => $this->canEdit ? [
                    'route' => [
                        'name'       => 'shops.show.products.create',
                        'parameters' => array_values([$shop->slug])
                    ],
                    'label' => __('product'),
                    'style' => 'create'
                ] : false
            ],
            prefix: 'products'
        )
        )->table(
        IndexCollection::make()->tableStructure(
            parent: $shop,
            prefix: 'collections'
        )
        );
        */
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
                        'organisation'=> $this->organisation->slug,
                        'shop'        => $shop->slug
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
