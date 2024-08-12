<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 11:40:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\UI;

use App\Actions\Helpers\Country\UI\GetCountriesOptions;
use App\Actions\Helpers\Currency\UI\GetCurrenciesOptions;
use App\Actions\Helpers\Language\UI\GetLanguagesOptions;
use App\Actions\OrgAction;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Exception;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class EditShop extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasAnyPermission(['org-admin.'.$this->organisation->id, 'shop-admin.'.$this->shop->id]);
    }

    public function handle(Shop $shop): Shop
    {
        return $shop;
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): Shop
    {
        $this->initialisationFromShop($shop, $request);
        return $this->handle($shop);
    }

    /**
     * @throws Exception
     */
    public function htmlResponse(Shop $shop, ActionRequest $request): Response
    {
        $shopify = Arr::get($shop->settings, 'shopify');

        return Inertia::render(
            'EditModel',
            [
                'title'        => __('edit shop'),
                'breadcrumbs'  => $this->getBreadcrumbs($request->route()->getName(), $request->route()->originalParameters()),

                'pageHead'    => [
                    'title'     => $shop->name,
                    'icon'      => [
                        'title' => __('Shop'),
                        'icon'  => 'fal fa-store-alt'
                    ],
                    'actions'   => [
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

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('Detail'),
                            'icon'   => 'fa-light fa-id-card',
                            'fields' => [
                                'code' => [
                                    'type'         => 'input',
                                    'label'        => __('code'),
                                    'value'        => $shop->code,
                                    'required'     => true,
                                ],
                                'name' => [
                                    'type'         => 'input',
                                    'label'        => __('name'),
                                    'value'        => $shop->name,
                                    'required'     => true,
                                ],
                                'type' => [
                                    'type'         => 'select',
                                    'label'        => __('type'),
                                    'value'        => $shop->type,
                                    'placeholder'  => __('Select an option'),
                                    'options'      => Options::forEnum(ShopTypeEnum::class),
                                    'required'     => true,
                                    'mode'         => 'single',
                                    'searchable'   => true
                                ]
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
                                    'searchable'    => true
                                ],
                                'currency_id' => [
                                    'type'          => 'select',
                                    'label'         => __('currency'),
                                    'placeholder'   => __('Select your currency'),
                                    'value'         => $shop->currency_id,
                                    'options'       => GetCurrenciesOptions::run(),
                                    'searchable'    => true
                                ],
                                'language_id' => [
                                    'type'          => 'select',
                                    'label'         => __('language'),
                                    'placeholder'   => __('Select your language'),
                                    'value'         => $shop->language_id,
                                    'options'       => GetLanguagesOptions::make()->all(),
                                    'searchable'    => true
                                ]
                            ],

                        ],
                        [
                            'title'  => __('contact/details'),
                            'icon'   => 'fa-light fa-user',
                            'fields' => [
                                'contact_name' => [
                                    'type'  => 'input',
                                    'label' => __('contact name'),
                                    'value' => $shop->contact_name,
                                ],
                                'company_name' => [
                                    'type'  => 'input',
                                    'label' => __('company name'),
                                    'value' => $shop->company_name,
                                ],
                                'email'        => [
                                    'type'    => 'input',
                                    'label'   => __('email'),
                                    'value'   => $shop->email,
                                    'options' => [
                                        'inputType' => 'email'
                                    ]
                                ],
                                'phone'        => [
                                    'type'  => 'phone',
                                    'label' => __('telephone'),
                                    'value' => $shop->phone,
                                ],
                            ]
                        ],
                        [
                            'title'  => __('shopify'),
                            'icon'   => 'fa-light fa-shopping-bag',
                            'fields' => [
                                'shopify_shop_name'  => [
                                    'type'          => 'input',
                                    'label'         => __('shop name'),
                                    'placeholder'   => __('Input your shop name'),
                                    'value'         => Arr::get($shopify, 'shop_name')
                                ],
                                'shopify_api_key' => [
                                    'type'          => 'password',
                                    'label'         => __('api key'),
                                    'placeholder'   => __('Input your api key'),
                                    'value'         => Arr::get($shopify, 'api_key')
                                ],
                                'shopify_api_secret' => [
                                    'type'          => 'password',
                                    'label'         => __('api secret'),
                                    'placeholder'   => __('Input your api secret'),
                                    'value'         => Arr::get($shopify, 'api_secret')
                                ],
                                'shopify_access_token' => [
                                    'type'          => 'password',
                                    'label'         => __('access token'),
                                    'placeholder'   => __('Input your access token'),
                                    'value'         => Arr::get($shopify, 'access_token')
                                ]
                            ],
                            'button' => [
                                'title' => 'Connect to Shopify',
                                'route' => [
                                    'name'       => 'grp.models.shopify.connect',
                                    'parameters' => [
                                        'shop' => $shop->slug
                                    ]
                                ]
                            ]
                        ],
                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'grp.models.org.shop.update',
                            'parameters' => [
                                'organisation' => $shop->organisation_id,
                                'shop'         => $shop->id
                            ]
                        ],
                    ]
                ],

            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return match ($routeName) {
            'grp.org.shops.show.settings.edit' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.shops.show.settings.edit',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Settings')
                        ]
                    ]
                ]
            ),
            default => []
        };
    }



}
