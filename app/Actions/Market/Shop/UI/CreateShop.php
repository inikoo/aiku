<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:30 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Market\Shop\UI;

use App\Actions\Assets\Country\UI\GetCountriesOptions;
use App\Actions\Assets\Currency\UI\GetCurrenciesOptions;
use App\Actions\Assets\Language\UI\GetLanguagesOptions;
use App\Actions\Assets\TimeZone\UI\GetTimeZonesOptions;
use App\Actions\InertiaAction;
use App\Enums\Market\Shop\ShopSubtypeEnum;
use App\Enums\Market\Shop\ShopTypeEnum;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateShop extends InertiaAction
{
    /**
     * @throws Exception
     */
    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('new shop'),
                'pageHead'    => [
                    'title'        => __('new shop'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'shops.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('detail'),
                            'fields' => [

                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                ],
                                'name' => [
                                    'type'   => 'input',
                                    'label'  => __('name'),
                                    'value'  => '',
                                    'options'=> [
                                        'counter'=> true
                                    ]
                                ],
                                'type' => [
                                    'type'         => 'select',
                                    'label'        => __('type'),
                                    'placeholder'  => 'Select a Type',
                                    'options'      => Options::forEnum(ShopTypeEnum::class),
                                    'mode'         => 'single',
                                    'required'     => true

                                ],
                                'subtype' => [
                                    'type'         => 'select',
                                    'label'        => __('subtype'),
                                    'placeholder'  => 'Select a Subtype',
                                    'options'      => Options::forEnum(ShopSubtypeEnum::class),
                                    'required'     => true,
                                    'mode'         => 'single'
                                ],
                            ]
                        ],

                        [
                            'title'  => __('localization'),
                            'icon'   => 'fa-light fa-phone',
                            'fields' => [
                                'country_id'  => [
                                    'type'        => 'select',
                                    'label'       => __('country'),
                                    'placeholder' => 'Select a Country',
                                    'options'     => GetCountriesOptions::run(),
                                    'required'    => true,
                                    'mode'        => 'single'
                                ],
                                'currency_id' => [
                                    'type'        => 'select',
                                    'label'       => __('currency'),
                                    'placeholder' => 'Select a Currency',
                                    'options'     => GetCurrenciesOptions::run(),
                                    'required'    => true,
                                    'mode'        => 'single'
                                ],
                                'timezone_id' => [
                                    'type'        => 'select',
                                    'label'       => __('timezone'),
                                    'placeholder' => 'Select a Timezone',
                                    'options'     => GetTimeZonesOptions::run(),
                                    'required'    => true,
                                    'mode'        => 'single'
                                ],
                                'language_id' => [
                                    'type'        => 'select',
                                    'label'       => __('language'),
                                    'placeholder' => 'Select a Language',
                                    'options'     => GetLanguagesOptions::make()->all(),
                                    'required'    => true,
                                    'mode'        => 'single'
                                ],
                            ]
                        ],
                        [
                            'title'  => __('contact/details'),
                            'fields' => [
                                'contact_name' => [
                                    'type'    => 'input',
                                    'label'   => __('contact name'),
                                    'value'   => '',
                                ],
                                'company_name' => [
                                    'type'    => 'input',
                                    'label'   => __('company name'),
                                    'value'   => '',
                                ],
                                'email' => [
                                    'type'    => 'input',
                                    'label'   => __('email'),
                                    'value'   => '',
                                    'options' => [
                                        'inputType' => 'email'
                                    ]
                                ],
                                'phone' => [
                                    'type'  => 'input',
                                    'label' => __('telephone'),
                                    'value' => ''
                                ],
                            ]
                        ],
                    ],
                    'route' => [
                        'name' => 'models.shop.store',
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
    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle();
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            IndexShops::make()->getBreadcrumbs(),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __("creating shop"),
                    ]
                ]
            ]
        );
    }
}
