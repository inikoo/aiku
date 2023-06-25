<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Marketplace\Agent\UI;

use App\Actions\Assets\Country\UI\GetAddressData;
use App\Actions\Assets\Country\UI\GetCountriesOptions;
use App\Actions\Assets\Currency\UI\GetCurrenciesOptions;
use App\Actions\InertiaAction;
use App\Http\Resources\Helpers\AddressFormFieldsResource;
use App\Models\Helpers\Address;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateMarketplaceAgent extends InertiaAction
{
    public function handle(): Response
    {

        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('new agent'),
                'pageHead'    => [
                    'title'        => __('new agent'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'procurement.marketplace.agents.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'fields' => [

                                'code' => [
                                    'type'    => 'input',
                                    'label'   => __('code'),
                                    'value'   => '',
                                    'required'=> true
                                ],
                                'company_name' => [
                                    'type'    => 'input',
                                    'label'   => __('company'),
                                    'value'   => '',
                                    'required'=> true
                                ],

                            ]
                        ],
                        [
                            'title'  => __('contact'),
                            'icon'   => 'fa-light fa-phone',
                            'fields' => [

                                'contact_name' => [
                                    'type'    => 'input',
                                    'label'   => __('contact name'),
                                    'value'   => '',
                                    'required'=> true
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
                                    'type'  => 'phone',
                                    'label' => __('phone'),
                                    'value' => ''
                                ],
                                'address' => [
                                    'type'  => 'address',
                                    'label' => __('Address'),
                                    'value' => AddressFormFieldsResource::make(
                                        new Address(
                                            [
                                                'country_id' => app('currentTenant')->country_id,

                                            ]
                                        )
                                    )->getArray(),
                                    'options' => [
                                        'countriesAddressData' => GetAddressData::run()

                                    ]
                                ],

                            ]
                        ],
                        [
                            'title'  => __('settings'),
                            'icon'   => 'fa-light fa-cog',
                            'fields' => [
                                'currency_id' => [
                                    'type'        => 'select',
                                    'label'       => __('currency'),
                                    'placeholder' => 'Select a Currency',
                                    'options'     => GetCurrenciesOptions::run(),
                                    'required'    => true,
                                    'mode'        => 'single'
                                ],

                                'default_product_country_origin' => [
                                    'type'        => 'select',
                                    'label'       => __("Product's country of origin"),
                                    'placeholder' => 'Select a Country',
                                    'options'     => GetCountriesOptions::run(),
                                    'mode'        => 'single'
                                ],
                            ]
                        ]





                    ],
                    'route' => [
                        'name' => 'models.agent.store',
                    ]
                ],
            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('procurement.edit');
    }


    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle();
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            IndexMarketplaceAgents::make()->getBreadcrumbs(),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __("creating agent"),
                    ]
                ]
            ]
        );
    }
}
