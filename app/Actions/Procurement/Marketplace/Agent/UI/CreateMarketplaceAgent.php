<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Marketplace\Agent\UI;

use App\Actions\Assets\Country\GetAddressData;
use App\Actions\InertiaAction;
use App\Http\Resources\Helpers\AddressResource;
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
                            'name'       => 'procurement.marketplace-agents.index',
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
                            'title'  => __('contact'),
                            'icon'   => 'fa-light fa-phone',
                            'fields' => [
                                'email' => [
                                    'type'    => 'input',
                                    'label'   => __('email'),
                                    'value'   => '',
                                    'options' => [
                                        'inputType' => 'email'
                                    ]
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

                            ]
                        ],
                        [
                            'title'  => __('telephones'),
                            'fields' => [

                                'mobile' => [
                                    'type'  => 'input',
                                    'label' => __('mobile'),
                                    'value' => ''
                                ],
                                'telephone' => [
                                    'type'  => 'input',
                                    'label' => __('telephone'),
                                    'value' => ''
                                ],
                                'fax' => [
                                    'type'  => 'input',
                                    'label' => __('fax'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        [
                            'title'  => __("supplier's products settings"),
                            'fields' => [

                                'products origin country code' => [
                                    'type'  => 'input',
                                    'label' => __('products origin country code'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        [
                            'title'  => __('waiting times'),
                            'fields' => [

                                'delivery time' => [
                                    'type'  => 'input',
                                    'label' => __('delivery time (days)'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        [
                            'title'  => __('payment'),
                            'fields' => [

                                'incoterm' => [
                                    'type'  => 'input',
                                    'label' => __('incoterm'),
                                    'value' => ''
                                ],
                                'currency' => [
                                    'type'  => 'input',
                                    'label' => __('currency'),
                                    'value' => ''
                                ],
                                'payment terms' => [
                                    'type'  => 'input',
                                    'label' => __('payment terms'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        [
                            'title'  => __('delivery'),
                            'fields' => [

                                'port of export' => [
                                    'type'  => 'input',
                                    'label' => __('port of export'),
                                    'value' => ''
                                ],
                                'port of import' => [
                                    'type'  => 'input',
                                    'label' => __('port of import'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        [
                            'title'  => __('terms and conditions'),
                            'fields' => [

                                't&c' => [
                                    'type'  => 'input',
                                    'label' => __('t&c'),
                                    'value' => ''
                                ],
                                'include general t&c' => [
                                    'type'  => 'input',
                                    'label' => __('include general t&c'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        [
                            'title'  => __('purchase order settings'),
                            'fields' => [

                                'order number format' => [
                                    'type'  => 'input',
                                    'label' => __('order number format'),
                                    'value' => ''
                                ],
                                'last incremental order number' => [
                                    'type'  => 'input',
                                    'label' => __('last incremental order number'),
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
