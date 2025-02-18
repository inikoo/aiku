<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 18 Dec 2024 23:47:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\UI\Catalogue\Rentals;

use App\Actions\Catalogue\Shop\UI\IndexShops;
use App\Actions\Catalogue\Shop\UI\ShowCatalogue;
use App\Actions\Fulfilment\UI\Catalogue\ShowFulfilmentCatalogueDashboard;
use App\Actions\OrgAction;
use App\Enums\Billables\Rental\RentalStateEnum;
use App\Enums\UI\Fulfilment\FulfilmentRentalTabsEnum;
use App\Http\Resources\Fulfilment\RentalsResource;
use App\Models\Billables\Rental;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowRental extends OrgAction
{
    private Organisation|Shop|Fulfilment|ProductCategory $parent;

    public function handle(Rental $rental): Rental
    {
        return $rental;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Fulfilment) {
            $this->canEdit   = $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");
            $this->canDelete = $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");
            return $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.view");
        } elseif ($this->parent instanceof Organisation) {
            $this->canEdit   = $request->user()->authTo("shops.{$this->organisation->id}.edit");
            $this->canDelete = $request->user()->authTo("shops.{$this->organisation->id}.edit");
            return $request->user()->authTo("shops.{$this->organisation->id}.view");
        } else {
            $this->canEdit   = $request->user()->authTo("products.{$this->shop->id}.edit");
            $this->canDelete = $request->user()->authTo("products.{$this->shop->id}.edit");
            return $request->user()->authTo("products.{$this->shop->id}.view");
        }


    }

    public function inOrganisation(Organisation $organisation, Rental $rental, ActionRequest $request): Rental
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request)->withTab(FulfilmentRentalTabsEnum::values());
        return $this->handle($rental);
    }

    public function asController(Organisation $organisation, Shop $shop, Rental $rental, ActionRequest $request): Rental
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request)->withTab(FulfilmentRentalTabsEnum::values());
        return $this->handle($rental);
    }

    public function inDepartment(Organisation $organisation, Shop $shop, ProductCategory $department, Rental $rental, ActionRequest $request): Rental
    {
        $this->parent = $department;
        $this->initialisationFromShop($shop, $request)->withTab(FulfilmentRentalTabsEnum::values());

        return $this->handle($rental);
    }

    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Rental $rental, ActionRequest $request): Rental
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(FulfilmentRentalTabsEnum::values());
        return $this->handle($rental);
    }

    public function htmlResponse(Rental $rental, ActionRequest $request): Response
    {
        // dd($rental);
        return Inertia::render(
            'Org/Fulfilment/Rental',
            [
                'title'       => __('rental'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'   => [
                    'previous' => $this->getPrevious($rental, $request),
                    'next'     => $this->getNext($rental, $request),
                ],
                'pageHead'    => [
                    'title'   => $rental->code,
                    'model'   => __('Rental'),
                    'icon'    =>
                        [
                            'icon'  => ['fal', 'fa-garage'],
                            'title' => __('rental')
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
                    ],
                    'iconRight' => RentalStateEnum::stateIcon()[$rental->state->value]
                ],
                'tabs' => [
                    'current'    => $this->tab,
                    'navigation' => FulfilmentRentalTabsEnum::navigation()
                ],


                FulfilmentRentalTabsEnum::SHOWCASE->value => $this->tab == FulfilmentRentalTabsEnum::SHOWCASE->value ?
                    fn () => GetRentalShowcase::run($rental)
                    : Inertia::lazy(fn () => GetRentalShowcase::run($rental)),
            ]
        );
    }

    public function jsonResponse(Rental $rental): RentalsResource
    {
        return new RentalsResource($rental);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = null): array
    {
        $headCrumb = function (Rental $rental, array $routeParameters, $suffix) {
            return [

                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('Rentals')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $rental->slug,
                        ],
                    ],
                    'suffix'         => $suffix,

                ],

            ];
        };

        $rental = Rental::where('slug', $routeParameters['rental'])->first();

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
                    $rental,
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
            'grp.org.fulfilments.show.catalogue.rentals.show' =>
            array_merge(
                (new ShowFulfilmentCatalogueDashboard())->getBreadcrumbs($routeParameters),
                $headCrumb(
                    $rental,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.catalogue.rentals.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.catalogue.rentals.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(Rental $rental, ActionRequest $request): ?array
    {
        $previous = Rental::where('slug', '<', $rental->slug)->orderBy('slug', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Rental $rental, ActionRequest $request): ?array
    {
        $next = Rental::where('slug', '>', $rental->slug)->orderBy('slug')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Rental $rental, string $routeName): ?array
    {
        if (!$rental) {
            return null;
        }

        return match ($routeName) {
            'grp.org.fulfilments.show.catalogue.rentals.show' => [
                'label' => $rental->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                       'organisation'   => $rental->organisation->slug,
                        'fulfilment'    => $rental->asset->shop->slug,
                        'rental'        => $rental->slug
                    ],
                ],
            ],
            default => null,
        };
    }
}
