<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 11:40:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Shop\UI;

use App\Actions\Assets\Country\UI\GetCountriesOptions;
use App\Actions\Assets\Currency\UI\GetCurrenciesOptions;
use App\Actions\Assets\Language\UI\GetLanguagesOptions;
use App\Actions\InertiaAction;
use App\Enums\Market\Shop\ShopSubtypeEnum;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Models\Market\Shop;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

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

    /**
     * @throws Exception
     */
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
                            'title'  => __('Detail'),
                            'icon'   => 'fa-light fa-id-card',
                            'fields' => [
                                'code' => [
                                    'type'     => 'input',
                                    'label'    => __('code'),
                                    'value'    => $shop->code,
                                ],
                                'name' => [
                                    'type'     => 'input',
                                    'label'    => __('label'),
                                    'value'    => $shop->name,
                                ],
                                'type' => [
                                    'type'         => 'select',
                                    'label'        => __('type'),
                                    'value'        => $shop->type,
                                    'placeholder'  => 'Select a Type',
                                    'options'      => Options::forEnum(ShopTypeEnum::class),
                                    'required'     => true,
                                    'mode'         => 'single',
                                ],
                                'subtype' => [
                                    'type'         => 'select',
                                    'label'        => __('subtype'),
                                    'value'        => $shop->subtype,
                                    'placeholder'  => 'Select a Subtype',
                                    'options'      => Options::forEnum(ShopSubtypeEnum::class),
                                    'required'     => true,
                                    'mode'         => 'single',
                                ],
                            ]
                        ],
                        [
                            'title'  => __('properties'),
                            'icon'   => 'fa-light fa-fingerprint',
                            'fields' => [
                                'country_id'  => [
                                    'type'          => 'select',
                                    'label'         => __('country'),
                                    'placeholder'   => __('Select your country'),
                                    'value'         => $shop->country_id,
                                    'options'       => GetCountriesOptions::run(),
                                ],
                                'currency_id' => [
                                    'type'          => 'select',
                                    'label'         => __('currency'),
                                    'placeholder'   => __('Select your currency'),
                                    'value'         => $shop->currency_id,
                                    'options'       => GetCurrenciesOptions::run(),
                                ],
                                'language_id' => [
                                    'type'          => 'select',
                                    'label'         => __('language'),
                                    'placeholder'   => __('Select your language'),
                                    'value'         => $shop->language_id,
                                    'options'       => GetLanguagesOptions::make()->all(),
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
        $routeParameters = ['shop' => $shop];
        return ShowShop::make()->getBreadcrumbs($routeParameters, suffix: '('.__('editing').')');
    }
}
