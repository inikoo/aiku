<?php

/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:09:31 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Fulfilment\UI\Catalogue\PhysicalGoods;

use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\WithFulfilmentShopEditAuthorisation;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class EditFulfilmentPhysicalGoods extends OrgAction
{
    use WithFulfilmentShopEditAuthorisation;

    public function handle(Product $product): Product
    {
        return $product;
    }


    public function asController(Organisation $organisation, Fulfilment $fulfilment, Product $product, ActionRequest $request): Product
    {
        $this->initialisationFromFulfilment($fulfilment, $request);
        return $this->handle($product);
    }

    /**
     * @throws \Exception
     */
    public function htmlResponse(Product $product, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('goods'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($product, $request),
                    'next'     => $this->getNext($product, $request),
                ],
                'pageHead'    => [
                    'title'    => $product->code,
                    'icon'     =>
                        [
                            'icon'  => ['fal', 'fa-cube'],
                            'title' => __('goods')
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
                                    'value'    => $product->code,
                                    'readonly' => true
                                ],
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('label'),
                                    'value' => $product->name,
                                ],
                                'description' => [
                                    'type'  => 'input',
                                    'label' => __('description'),
                                    'value' => $product->description
                                ],
                                'unit' => [
                                    'type'     => 'input',
                                    'label'    => __('unit'),
                                    'value'    => $product->unit,
                                ],
                                'units' => [
                                    'type'     => 'input',
                                    'label'    => __('units'),
                                    'value'    => $product->units,
                                ],
                                'price' => [
                                    'type'    => 'input',
                                    'label'   => __('price'),
                                    'required' => true,
                                    'value'   => $product->price
                                ],
                                'state' => [
                                    'type'    => 'select',
                                    'label'   => __('state'),
                                    'required' => true,
                                    'value'   => $product->state,
                                    'options' => Options::forEnum(AssetStateEnum::class)
                                ],

                            ]
                        ]

                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'grp.models.product.update',
                            'parameters' => $product->id

                        ],
                    ]
                ]

            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowFulfilmentPhysicalGood::make()->getBreadcrumbs(
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('Editing').')'
        );
    }

    public function getPrevious(Product $product, ActionRequest $request): ?array
    {
        $previous = Product::where('shop_id', $this->shop->id)->where('slug', '<', $product->slug)->orderBy('slug', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Product $product, ActionRequest $request): ?array
    {
        $next = Product::where('shop_id', $this->shop->id)->where('slug', '>', $product->slug)->orderBy('slug')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Product $product, string $routeName): ?array
    {
        if (!$product) {
            return null;
        }
        return match ($routeName) {
            'grp.org.fulfilments.show.catalogue.physical_goods.edit' => [
                'label' => $product->name,
                'route' => [
                    'name'      => $routeName,
                    'parameters' => [
                        'organisation' => $this->organisation->slug,
                        'fulfilment' => $this->fulfilment->slug,
                        'product' => $product->slug
                    ]

                ]
            ],
            default => null,
        };
    }
}
