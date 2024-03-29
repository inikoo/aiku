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
use App\Actions\OrgAction;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateShop extends OrgAction
{
    public function handle()
    {

    }

    /**
     * @throws \Exception
     */
    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __('new shop'),
                'pageHead'    => [
                    'title'   => __('new shop'),
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => 'grp.org.shops.index',
                                'parameters' => $request->route()->originalParameters()
                            ],
                        ]
                    ]
                ],
                'formData'    => [
                    'blueprint' => [
                        [
                            'title'  => __('detail'),
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
                                'type' => [
                                    'type'        => 'select',
                                    'label'       => __('type'),
                                    'placeholder' => __('Select one option'),
                                    'options'     => Options::forEnum(ShopTypeEnum::class),
                                    'required'    => true,
                                    'mode'        => 'single',
                                    'searchable'  => true
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
                                    'placeholder' => __('Select a country'),
                                    'options'     => GetCountriesOptions::run(),
                                    'value'       => app('currentTenant')->country_id,
                                    'required'    => true,
                                    'mode'        => 'single'
                                ],
                                'language_id' => [
                                    'type'        => 'select',
                                    'label'       => __('language'),
                                    'placeholder' => __('Select a language'),
                                    'options'     => GetLanguagesOptions::make()->all(),
                                    'value'       => app('currentTenant')->language_id,
                                    'required'    => true,
                                    'mode'        => 'single'
                                ],
                                'currency_id' => [
                                    'type'        => 'select',
                                    'label'       => __('currency'),
                                    'placeholder' => __('Select a currency'),
                                    'options'     => GetCurrenciesOptions::run(),
                                    'value'       => app('currentTenant')->currency_id,
                                    'required'    => true,
                                    'mode'        => 'single'
                                ],
                                'timezone_id' => [
                                    'type'        => 'select',
                                    'label'       => __('timezone'),
                                    'placeholder' => __('Select a timezone'),
                                    'options'     => GetTimeZonesOptions::run(),
                                    'value'       => app('currentTenant')->timezone_id,
                                    'required'    => true,
                                    'mode'        => 'single'
                                ],

                            ]
                        ],
                        [
                            'title'  => __('contact/details'),
                            'fields' => [
                                'contact_name' => [
                                    'type'  => 'input',
                                    'label' => __('contact name'),
                                    'value' => '',
                                ],
                                'company_name' => [
                                    'type'  => 'input',
                                    'label' => __('company name'),
                                    'value' => '',
                                ],
                                'email'        => [
                                    'type'    => 'input',
                                    'label'   => __('email'),
                                    'value'   => '',
                                    'options' => [
                                        'inputType' => 'email'
                                    ]
                                ],
                                'phone'        => [
                                    'type'  => 'phone',
                                    'label' => __('telephone'),
                                    'value' => ''
                                ],
                            ]
                        ],
                    ],
                    'route'     => [
                        'name' => 'grp.models.shop.store',
                    ]
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('shops.edit');
    }



    public function asController(Organisation $organisation, ActionRequest $request): ActionRequest
    {
        $this->initialisation($organisation, $request);

        return $request;
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            IndexShops::make()->getBreadcrumbs('grp.org.shops.index', $routeParameters),
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
