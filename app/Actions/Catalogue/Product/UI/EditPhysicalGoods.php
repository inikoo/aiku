<?php

/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:09:31 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\Product\UI;

use App\Actions\OrgAction;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class EditPhysicalGoods extends OrgAction
{
    public function handle(Product $product): Product
    {
        return $product;
    }

    public function authorize(ActionRequest $request): bool
    {
        // dd($this->shop);
        $this->canEdit   = $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        $this->canDelete = $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");
        return $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.view");
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

    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Product $product, ActionRequest $request): Product
    {
        $this->parent = $fulfilment;
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
        return ShowPhysicalGoods::make()->getBreadcrumbs(
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('Editing').')'
        );
    }

    public function getPrevious(Product $product, ActionRequest $request): ?array
    {
        $previous = Product::where('slug', '<', $product->slug)->orderBy('slug', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Product $product, ActionRequest $request): ?array
    {
        $next = Product::where('slug', '>', $product->slug)->orderBy('slug')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Product $product, string $routeName): ?array
    {
        if (!$product) {
            return null;
        }
        return match ($routeName) {
            'shops.products.edit' => [
                'label' => $product->name,
                'route' => [
                    'name'      => $routeName,
                    'parameters' => [
                        'product' => $product->slug
                    ]

                ]
            ],
            'shops.show.products.edit' => [
                'label' => $product->name,
                'route' => [
                    'name'      => $routeName,
                    'parameters' => [
                        'shop'   => $product->shop->slug,
                        'product' => $product->slug
                    ]

                ]
            ],
            default => null,
        };
    }
}
