<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 11:40:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\UI;

use App\Actions\Assets\Country\UI\GetCountriesOptions;
use App\Actions\Assets\Currency\UI\GetCurrenciesOptions;
use App\Actions\Assets\Language\UI\GetLanguagesOptions;
use App\Actions\InertiaAction;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Shop;
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
    public function htmlResponse(Shop $shop, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'        => __('edit shop'),
                'breadcrumbs'  => $this->getBreadcrumbs($shop),
                'navigation'   => [
                    'previous' => $this->getPrevious($shop, $request),
                    'next'     => $this->getNext($shop, $request),
                ],
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
                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'grp.models.shop.update',
                            'parameters' => $shop->id

                        ],
                    ]
                ],

            ]
        );
    }


    public function getBreadcrumbs(Shop $shop): array
    {
        $routeParameters = ['shop' => $shop];
        return ShowShop::make()->getBreadcrumbs($routeParameters, suffix: '('.__('editing').')');
    }

    public function getPrevious(Shop $shop, ActionRequest $request): ?array
    {
        $previous = Shop::where('code', '<', $shop->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(Shop $shop, ActionRequest $request): ?array
    {
        $next = Shop::where('code', '>', $shop->code)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Shop $shop, string $routeName): ?array
    {
        if (!$shop) {
            return null;
        }

        return match ($routeName) {
            'shops.edit' => [
                'label' => $shop->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'shop' => $shop->slug
                    ]

                ]
            ]
        };
    }
}
