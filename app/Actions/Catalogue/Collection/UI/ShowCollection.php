<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:05:53 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\Collection\UI;

use App\Actions\Catalogue\Shop\UI\IndexShops;
use App\Actions\Catalogue\Shop\UI\ShowCatalogue;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HaCatalogueAuthorisation;
use App\Enums\UI\Catalogue\CollectionTabsEnum;
use App\Http\Resources\Catalogue\CollectionResource;
use App\Models\Catalogue\Collection;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowCollection extends OrgAction
{
    use HaCatalogueAuthorisation;

    private Organisation|Shop $parent;

    public function handle(Collection $collection): Collection
    {
        return $collection;
    }

    public function inOrganisation(Organisation $organisation, Collection $collection, ActionRequest $request): Collection
    {
        $this->parent= $organisation;
        $this->initialisation($organisation, $request)->withTab(CollectionTabsEnum::values());
        return $this->handle($collection);
    }

    public function asController(Organisation $organisation, Shop $shop, Collection $collection, ActionRequest $request): Collection
    {
        $this->parent= $shop;
        $this->initialisationFromShop($shop, $request)->withTab(CollectionTabsEnum::values());
        return $this->handle($collection);
    }

    public function htmlResponse(Collection $collection, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Catalogue/Collection',
            [
                'title'       => __('collection'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($collection, $request),
                    'next'     => $this->getNext($collection, $request),
                ],
                'pageHead'    => [
                    'title'     => $collection->code,
                    'model'     => __('collection'),
                    'icon'      =>
                        [
                            'icon'  => ['fal', 'fa-cube'],
                            'title' => __('collection')
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
                    'navigation' => CollectionTabsEnum::navigation()
                ],


                CollectionTabsEnum::SHOWCASE->value => $this->tab == CollectionTabsEnum::SHOWCASE->value ?
                    fn () => GetCollectionShowcase::run($collection)
                    : Inertia::lazy(fn () => GetCollectionShowcase::run($collection)),

                // ProductTabsEnum::ORDERS->value => $this->tab == ProductTabsEnum::ORDERS->value ?
                //     fn () => OrderResource::collection(IndexOrders::run($product))
                //     : Inertia::lazy(fn () => OrderResource::collection(IndexOrders::run($product))),

                // ProductTabsEnum::CUSTOMERS->value => $this->tab == ProductTabsEnum::CUSTOMERS->value ?
                //     fn () => CustomersResource::collection(IndexCustomers::run($product))
                //     : Inertia::lazy(fn () => CustomersResource::collection(IndexCustomers::run($product))),

                // ProductTabsEnum::MAILSHOTS->value => $this->tab == ProductTabsEnum::MAILSHOTS->value ?
                //     fn () => MailshotResource::collection(IndexMailshots::run($product))
                //     : Inertia::lazy(fn () => MailshotResource::collection(IndexMailshots::run($product))),

                /*
                ProductTabsEnum::IMAGES->value => $this->tab == ProductTabsEnum::IMAGES->value ?
                    fn () => ImagesResource::collection(IndexImages::run($product))
                    : Inertia::lazy(fn () => ImagesResource::collection(IndexImages::run($product))),
                */

            ]
        );
    }

    public function jsonResponse(Collection $collection): CollectionResource
    {
        return new CollectionResource($collection);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (Collection $collection, array $routeParameters, $suffix) {
            return [

                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('Collections')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $collection->slug,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],

            ];
        };

        $collection=Collection::where('slug', $routeParameters['collection'])->first();

        return match ($routeName) {
            'shops.collections.show' =>
            array_merge(
                IndexShops::make()->getBreadcrumbs('grp.org.shops.index', $routeParameters['organisation']),
                $headCrumb(
                    $routeParameters['collection'],
                    [
                        'index' => [
                            'name'       => 'shops.collections.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'shops.collections.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.shops.show.catalogue.collections.show' =>
            array_merge(
                ShowCatalogue::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $collection,
                    [
                        'index' => [
                            'name'       => 'grp.org.shops.show.catalogue.collections.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.shops.show.catalogue.collections.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(Collection $collection, ActionRequest $request): ?array
    {
        $previous = Collection::where('slug', '<', $collection->slug)->orderBy('slug', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Collection $collection, ActionRequest $request): ?array
    {
        $next = Collection::where('slug', '>', $collection->slug)->orderBy('slug')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Collection $collection, string $routeName): ?array
    {
        if(!$collection) {
            return null;
        }

        return match ($routeName) {
            'shops.org.collections.show'=> [
                'label'=> $collection->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'collection'=> $collection->slug
                    ]

                ]
            ],
            'grp.org.shops.show.catalogue.collections.show'=> [
                'label'=> $collection->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'organisation'   => $this->organisation->slug,
                        'shop'           => $collection->shop->slug,
                        'collection'     => $collection->slug
                    ]

                ]
            ],
        };
    }
}
