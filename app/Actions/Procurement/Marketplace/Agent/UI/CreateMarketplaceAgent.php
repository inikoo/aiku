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
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => 'grp.procurement.marketplace.agents.index',
                                'parameters' => array_values($this->originalParameters)
                            ],
                        ]
                    ]
                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('ID/contact details'),
                            'icon'   => 'fal fa-address-book',
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
                                    'placeholder' => __('Select a currency'),
                                    'options'     => GetCurrenciesOptions::run(),
                                    'required'    => true,
                                    'mode'        => 'single',
                                    'searchable'  => true
                                ],

                                'default_product_country_origin' => [
                                    'type'        => 'select',
                                    'label'       => __("Product's country of origin"),
                                    'placeholder' => __('Select a country'),
                                    'options'     => GetCountriesOptions::run(),
                                    'mode'        => 'single',
                                    'searchable'  => true
                                ],
                            ]
                        ]





                    ],
                    'route' => [
                        'name' => 'grp.models.agent.store',
                    ]
                ],
            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('procurement.edit');
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
