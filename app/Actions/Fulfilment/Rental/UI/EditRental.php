<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:09:31 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Fulfilment\Rental\UI;

use App\Actions\OrgAction;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Fulfilment\Rental\RentalUnitEnum;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\Rental;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class EditRental extends OrgAction
{
    public function handle(Rental $rental): Rental
    {
        return $rental;
    }

    public function authorize(ActionRequest $request): bool
    {
        // dd($this->shop);
        $this->canEdit   = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
    }

    // public function inOrganisation(Asset $product, ActionRequest $request): Asset
    // {
    //     $this->initialisation($request);

    //     return $this->handle($product);
    // }

    // /** @noinspection PhpUnusedParameterInspection */
    // public function inShop(Shop $shop, Asset $product, ActionRequest $request): Asset
    // {
    //     $this->initialisation($request);

    //     return $this->handle($product);
    // }

    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Rental $rental, ActionRequest $request): Rental
    {
        $this->parent= $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);
        return $this->handle($rental);
    }

    /**
     * @throws \Exception
     */
    public function htmlResponse(Rental $rental, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('rental'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($rental, $request),
                    'next'     => $this->getNext($rental, $request),
                ],
                'pageHead'    => [
                    'title'    => $rental->code,
                    'icon'     =>
                        [
                            'icon'  => ['fal', 'fa-cube'],
                            'title' => __('rental')
                        ],
                    'actions'  => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ]
                ],
                'formData'    => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'fields' => [
                                'code' => [
                                    'type'     => 'input',
                                    'label'    => __('code'),
                                    'value'    => $rental->code,
                                    'readonly' => true
                                ],
                                'name' => [
                                    'type'     => 'input',
                                    'label'    => __('label'),
                                    'value'    => $rental->name,
                                    'readonly' => true
                                ],
                                'description' => [
                                    'type'  => 'input',
                                    'label' => __('description'),
                                    'value' => $rental->description
                                ],
                                'unit' => [
                                    'type'     => 'select',
                                    'label'    => __('unit'),
                                    'value'    => $rental->unit,
                                    'options'  => Options::forEnum(RentalUnitEnum::class)
                                ],
                                'units' => [
                                    'type'     => 'input',
                                    'label'    => __('units'),
                                    'value'    => $rental->units,
                                ],
                                'price' => [
                                    'type'    => 'input',
                                    'label'   => __('price'),
                                    'required'=> true,
                                    'value'   => $rental->price
                                ],
                                // 'type' => [
                                //     'type'          => 'select',
                                //     'label'         => __('type'),
                                //     'placeholder'   => 'Select a Asset Type',
                                //     'options'       => Options::forEnum(AssetTypeEnum::class)->toArray(),
                                //     'required'      => true,
                                //     'mode'          => 'single',
                                //     'value'         => $product->type
                                // ]
                            ]
                        ]

                    ],
                    // Make sure to change the FulfilmentUITest after fixing this
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'grp.models.product.update',
                            'parameters' => $rental->id

                        ],
                    ]
                ]

            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowRental::make()->getBreadcrumbs(
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('Editing').')'
        );
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
        if(!$rental) {
            return null;
        }
        return match ($routeName) {
            'shops.products.edit'=> [
                'label'=> $rental->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'product'=> $rental->slug
                    ]

                ]
            ],
            'shops.show.products.edit'=> [
                'label'=> $rental->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'shop'   => $rental->shop->slug,
                        'product'=> $rental->slug
                    ]

                ]
            ],
            default => null,
        };
    }
}
