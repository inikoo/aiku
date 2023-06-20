<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:34:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Market\Product\UI;

use App\Actions\InertiaAction;
use App\Enums\Market\Product\ProductTypeEnum;
use App\Models\Market\Shop;
use App\Models\Tenancy\Tenant;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateProduct extends InertiaAction
{
    /**
     * @throws Exception
     */
    public function handle(Tenant|Shop $shop, ActionRequest $request): Response
    {

        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('new product'),
                'pageHead'    => [
                    'title'        => __('new product'),
                    'cancelCreate' => [
                        'route' => [
                            'name' => match ($this->routeName) {
                                'shops.show.products.create'    => 'shops.show.products.index',
                                'shops.products.create'         => 'shops',
                                default                         => preg_replace('/create$/', 'index', $this->routeName)
                            },
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ]

                ],
                'formData'    => [
                    'blueprint' =>
                        [
                            [
                                'title'  => __('name'),
                                'fields' => [
                                    'code' => [
                                        'type'  => 'input',
                                        'label' => __('code')
                                    ],
                                    'name' => [
                                        'type'  => 'input',
                                        'label' => __('name')
                                    ],

                                    'description' => [
                                        'type'  => 'input',
                                        'label' => __('description')
                                    ],
                                    'units' => [
                                        'type'  => 'input',
                                        'label' => __('units')
                                    ],
                                    'price' => [
                                        'type'  => 'input',
                                        'label' => __('price')
                                    ],
                                    'owner_id' => [
                                        'type'  => 'input',
                                        'label' => __('owner id')
                                    ],
                                    'owner_type' => [
                                        'type'  => 'input',
                                        'label' => __('owner type')
                                    ],
                                    'type' => [
                                        'type'    => 'select',
                                        'label'   => __('type'),
                                        'options' => Options::forEnum(ProductTypeEnum::class)->toArray(),
                                        'required'=> true,
                                        'mode'    => 'single'
                                    ]

                                ]
                            ]
                        ],
                    'route' => match ($this->routeName) {
                        'shops.show.products.create' => [
                            'name'      => 'models.shop.product.store',
                            'arguments' => [$shop->slug]
                        ],
                        default => [
                            'name' => 'models.product.store'
                        ]
                    }
                ]

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('shops.products.edit');
    }


    /**
     * @throws Exception
     */
    public function asController(Shop $shop, ActionRequest $request): Response
    {
        $this->initialisation($request);
        return $this->handle($shop, $request);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            IndexProducts::make()->getBreadcrumbs(
                routeName: preg_replace('/create$/', 'index', $routeName),
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'         => 'creatingModel',
                    'creatingModel'=> [
                        'label'=> __('creating product'),
                    ]
                ]
            ]
        );
    }

}
