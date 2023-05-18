<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:30 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Shop\UI;

use App\Actions\Assets\Country\GetAddressData;
use App\Actions\InertiaAction;
use App\Actions\Inventory\StockFamily\UI\HasUIStockFamilies;
use App\Http\Resources\Helpers\AddressResource;
use App\Models\Helpers\Address;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateShop extends InertiaAction
{
    use HasUIStockFamilies;


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
                            'title'  => __('id'),
                            'fields' => [

                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => ''
                                ],
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('name'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        [
                            'title'  => __('localization'),
                            'icon'   => 'fa-light fa-phone',
                            'fields' => [
                                'language' => [
                                    'type'    => 'input',
                                    'label'   => __('language'),
                                    'value'   => '',
                                ],
                                'currency' => [
                                    'type'    => 'input',
                                    'label'   => __('currency'),
                                    'value'   => '',
                                ],
                                'timezone' => [
                                    'type'    => 'input',
                                    'label'   => __('timezone'),
                                    'value'   => '',
                                ],

                            ]
                        ],
                        [
                            'title'  => __('contact/details'),
                            'fields' => [

                                'email' => [
                                    'type'    => 'input',
                                    'label'   => __('email'),
                                    'value'   => '',
                                    'options' => [
                                        'inputType' => 'email'
                                    ]
                                ],
                                'telephone' => [
                                    'type'  => 'input',
                                    'label' => __('telephone'),
                                    'value' => ''
                                ],
                                'address' => [
                                    'type'  => 'address',
                                    'label' => __('Address'),
                                    'value' => AddressResource::make(
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
                                'companyName' => [
                                    'type'  => 'input',
                                    'label' => __('company name'),
                                    'value' => ''
                                ],
                                'website' => [
                                    'type'  => 'input',
                                    'label' => __('website URL'),
                                    'value' => ''
                                ],
                                'companyNumber' => [
                                    'type'  => 'input',
                                    'label' => __('company number'),
                                    'value' => ''
                                ],
                                'vat' => [
                                    'type'  => 'input',
                                    'label' => __('VAT number'),
                                    'value' => ''
                                ],
                            ]
                        ],

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
        return $request->user()->can('shops');
    }


    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle();
    }
}
