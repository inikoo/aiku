<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:34:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Catalogue\Billable\UI;

use App\Actions\InertiaAction;
use App\Enums\Catalogue\Billable\BillableTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
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
    public function handle(Organisation|Shop $shop, ActionRequest $request): Response
    {

        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('new product'),
                'pageHead'    => [
                    'title'        => __('new product'),
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name' => match ($request->route()->getName()) {
                                    'shops.show.products.create'    => 'shops.show.products.index',
                                    'shops.products.create'         => 'shops',
                                    default                         => preg_replace('/create$/', 'index', $request->route()->getName())
                                },
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
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
                                        'type'      => 'input',
                                        'label'     => __('code'),
                                        'required'  => true
                                    ],
                                    'name' => [
                                        'type'      => 'input',
                                        'label'     => __('name'),
                                        'required'  => true
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
                                        'type'    => 'input',
                                        'label'   => __('price'),
                                        'required'=> true,
                                    ],
                                    'type' => [
                                        'type'          => 'select',
                                        'label'         => __('type'),
                                        'placeholder'   => 'Select a Billable Type',
                                        'options'       => Options::forEnum(BillableTypeEnum::class)->toArray(),
                                        'required'      => true,
                                        'mode'          => 'single'
                                    ]

                                ]
                            ]
                        ],
                    'route' => match ($request->route()->getName()) {
                        'shops.show.products.create' => [
                            'name'      => 'grp.models.show.product.store',
                            'arguments' => [$shop->id]
                        ],
                        default => [
                            'name' => 'grp.models.product.store'
                        ]
                    }
                ]

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('shops.products.edit');
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
                        'label'=> __('Creating product'),
                    ]
                ]
            ]
        );
    }

}
