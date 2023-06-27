<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:30 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\InertiaAction;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Models\Market\Shop;
use Exception;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateWebsite extends InertiaAction
{
    /**
     * @throws Exception
     */
    public function handle(Shop $shop, ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    routeParameters: $request->route()->parameters
                ),
                'title'       => __('new website'),
                'pageHead'    => [
                    'title'        => __('website'),
                    'container'    => [
                        'icon'    => ['fal', 'fa-store-alt'],
                        'tooltip' => __('Shop'),
                        'label'   => Str::possessive($shop->name)
                    ],
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'shops.show',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],
                'formData'    => [
                    'blueprint' => [
                        [
                            'title'  => __('domain'),
                            'fields' => [

                                'domain' => [
                                    'type'     => 'input',
                                    'label'    => __('domain'),
                                    'required' => true,
                                ],


                            ]
                        ],
                        [
                            'title'  => __('ID/name'),
                            'fields' => [

                                'code' => [
                                    'type'     => 'input',
                                    'label'    => __('code'),
                                    'required' => true,
                                ],
                                'name' => [
                                    'type'     => 'input',
                                    'label'    => __('name'),
                                    'required' => true,
                                    'value'    => '',
                                ],


                            ]
                        ],


                    ],
                    'route'     => [
                        'name'     => 'models.shop.website.create',
                        'arguments'=> [$shop->slug]
                    ]
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('shops.edit');
    }


    /**
     * @throws Exception
     */
    public function asController(Shop $shop, ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle($shop, $request);
    }

    public function getBreadcrumbs($routeParameters): array
    {
        return array_merge(
            ShowShop::make()->getBreadcrumbs(
                $routeParameters
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __("creating website"),
                    ]
                ]
            ]
        );
    }
}
