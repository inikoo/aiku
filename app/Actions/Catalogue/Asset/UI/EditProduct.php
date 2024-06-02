<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:09:31 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\Asset\UI;

use App\Actions\InertiaAction;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditProduct extends InertiaAction
{
    public function handle(Asset $product): Asset
    {
        return $product;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('shops.products.edit');

        return $request->user()->hasPermissionTo("shops.products.edit");
    }

    public function inOrganisation(Asset $product, ActionRequest $request): Asset
    {
        $this->initialisation($request);

        return $this->handle($product);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, Asset $product, ActionRequest $request): Asset
    {
        $this->initialisation($request);

        return $this->handle($product);
    }

    /**
     * @throws \Exception
     */
    public function htmlResponse(Asset $product, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('product'),
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
                            'title' => __('product')
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
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => $product->code
                                ],
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('label'),
                                    'value' => $product->name
                                ],
                                'description' => [
                                    'type'  => 'input',
                                    'label' => __('description'),
                                    'value' => $product->description
                                ],
                                'units' => [
                                    'type'  => 'input',
                                    'label' => __('units'),
                                    'value' => $product->units
                                ],
                                'price' => [
                                    'type'    => 'input',
                                    'label'   => __('price'),
                                    'required'=> true,
                                    'value'   => $product->price
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
        return ShowProduct::make()->getBreadcrumbs(
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('Editing').')'
        );
    }

    public function getPrevious(Asset $product, ActionRequest $request): ?array
    {
        $previous = Asset::where('slug', '<', $product->slug)->orderBy('slug', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Asset $product, ActionRequest $request): ?array
    {
        $next = Asset::where('slug', '>', $product->slug)->orderBy('slug')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Asset $product, string $routeName): ?array
    {
        if(!$product) {
            return null;
        }
        return match ($routeName) {
            'shops.products.edit'=> [
                'label'=> $product->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'product'=> $product->slug
                    ]

                ]
            ],
            'shops.show.products.edit'=> [
                'label'=> $product->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'shop'   => $product->shop->slug,
                        'product'=> $product->slug
                    ]

                ]
            ],
        };
    }
}
