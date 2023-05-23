<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 11:40:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Shop\UI;

use App\Actions\Assets\Country\UI\GetCountriesOptions;
use App\Actions\Assets\Currency\UI\GetCurrenciesOptions;
use App\Actions\Assets\Language\UI\GetLanguagesOptions;
use App\Actions\InertiaAction;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditShop extends InertiaAction
{
    public function handle(Shop $shop): Shop
    {
        return $shop;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("shops.edit");
    }

    public function asController(Shop $shop, ActionRequest $request): Shop
    {
        $this->initialisation($request);

        return $this->handle($shop);
    }


    public function htmlResponse(Shop $shop): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('edit shop'),
                'breadcrumbs' => $this->getBreadcrumbs($shop),
                'pageHead'    => [
                    'title'    => $shop->name,
                    'exitEdit' => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ],


                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'icon'   => 'fa-light fa-id-card',
                            'fields' => [
                                'code' => [
                                    'type'     => 'input',
                                    'label'    => __('code'),
                                    'value'    => $shop->code,
                                    'required' => true
                                ],
                                'name' => [
                                    'type'     => 'input',
                                    'label'    => __('label'),
                                    'value'    => $shop->name,
                                    'required' => true
                                ],
                            ]
                        ],
                        [
                            'title'  => __('properties'),
                            'icon'   => 'fa-light fa-fingerprint',
                            'fields' => [
                                'country_id'  => [
                                    'type'     => 'country',
                                    'label'    => __('country'),
                                    'value'    => $shop->country_id,
                                    'options'  => GetCountriesOptions::run(),
                                    'required' => true

                                ],
                                'currency_id' => [
                                    'type'     => 'currency',
                                    'label'    => __('currency'),
                                    'value'    => $shop->currency_id,
                                    'options'  => GetCurrenciesOptions::run(),
                                    'required' => true
                                ],
                                'language_id' => [
                                    'type'     => 'language',
                                    'label'    => __('language'),
                                    'value'    => $shop->language_id,
                                    'options'  => GetLanguagesOptions::run(),
                                    'required' => true
                                ],


                            ]
                        ]

                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'models.shop.update',
                            'parameters' => $shop->slug

                        ],
                    ]
                ]
            ]
        );
    }


    public function getBreadcrumbs(Shop $shop): array
    {
        return ShowShop::make()->getBreadcrumbs(shop: $shop, suffix: '('.__('editing').')');
    }
}
