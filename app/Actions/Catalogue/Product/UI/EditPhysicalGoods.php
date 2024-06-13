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
use App\Enums\Fulfilment\Rental\RentalUnitEnum;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Service;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\Rental;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class EditPhysicalGoods extends OrgAction
{
    public function handle(Asset $asset): Asset
    {
        return $asset;
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

    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, Asset $asset, ActionRequest $request): Asset
    {
        $this->parent= $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);
        return $this->handle($asset);
    }

    /**
     * @throws \Exception
     */
    public function htmlResponse(Asset $asset, ActionRequest $request): Response
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
                    'previous' => $this->getPrevious($asset, $request),
                    'next'     => $this->getNext($asset, $request),
                ],
                'pageHead'    => [
                    'title'    => $asset->code,
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
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => $asset->code,
                                    'readonly' => true
                                ],
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('label'),
                                    'value' => $asset->name,
                                ],
                                'description' => [
                                    'type'  => 'input',
                                    'label' => __('description'),
                                    'value' => $asset->description
                                ],
                                'unit' => [
                                    'type'     => 'input',
                                    'label'    => __('unit'),
                                    'value'    => $asset->unit,
                                ],
                                'units' => [
                                    'type'     => 'input',
                                    'label'    => __('units'),
                                    'value'    => $asset->units,
                                ],
                                'price' => [
                                    'type'    => 'input',
                                    'label'   => __('price'),
                                    'required'=> true,
                                    'value'   => $asset->price
                                ],
                                'state' => [
                                    'type'    => 'select',
                                    'label'   => __('state'),
                                    'required'=> true,
                                    'value'   => $asset->state,
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
                            'parameters' => $asset->id

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
        if(!$asset) {
            return null;
        }
        return match ($routeName) {
            'shops.products.edit'=> [
                'label'=> $asset->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'product'=> $asset->slug
                    ]

                ]
            ],
            'shops.show.products.edit'=> [
                'label'=> $asset->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'shop'   => $asset->shop->slug,
                        'product'=> $asset->slug
                    ]

                ]
            ],
            default => null,
        };
    }
}
