<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Marketplace\Supplier\UI;

use App\Actions\Assets\Country\UI\GetAddressData;
use App\Actions\InertiaAction;
use App\Actions\Procurement\Marketplace\Agent\UI\ShowMarketplaceAgent;
use App\Http\Resources\Helpers\AddressFormFieldsResource;
use App\Models\Helpers\Address;
use App\Models\Procurement\Agent;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateMarketplaceSupplier extends InertiaAction
{
    public function handle(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('new supplier'),
                'pageHead'    => [
                    'title'        => __('new supplier'),
                    'cancelCreate' => [
                        'route' => match ($request->route()->getName()) {
                            'procurement.marketplace.agents.show.suppliers.create' =>
                            [
                                'name'       => 'procurement.marketplace.agents.show',
                                'parameters' => array_values($this->originalParameters)
                            ],
                            default => [
                                'name'       => 'procurement.marketplace.suppliers.index',
                                'parameters' => array_values($this->originalParameters)
                            ],
                        }


                    ]

                ],
                'formData'    => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'fields' => [

                                'code' => [
                                    'type'     => 'input',
                                    'label'    => __('code'),
                                    'value'    => '',
                                    'required' => true,
                                ],
                            ]
                        ],
                        [
                            'title'  => __('type'),
                            'fields' => [

                                'delivery_type' => [
                                    'type'  => 'input',
                                    'label' => __('delivery type'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        /*
                        [
                            'title'  => __('our id in supplier records'),
                            'fields' => [

                                'account number' => [
                                    'type'  => 'input',
                                    'label' => __('account number'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        */

                        [
                            'title'  => __('contact'),
                            'icon'   => 'fa-light fa-phone',
                            'fields' => [
                                'contact_name' => [
                                    'type'  => 'input',
                                    'label' => __('contact name'),
                                    'value' => ''
                                ],
                                'company_name' => [
                                    'type'  => 'input',
                                    'label' => __('company name'),
                                    'value' => ''
                                ],
                                'email'        => [
                                    'type'    => 'input',
                                    'label'   => __('email'),
                                    'value'   => '',
                                    'options' => [
                                        'inputType' => 'email'
                                    ]
                                ],
                                'address'      => [
                                    'type'    => 'address',
                                    'label'   => __('Address'),
                                    'value'   => AddressFormFieldsResource::make(
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

                                'mobile'    => [
                                    'type'  => 'input',
                                    'label' => __('mobile'),
                                    'value' => ''
                                ],
                                'telephone' => [
                                    'type'  => 'input',
                                    'label' => __('telephone'),
                                    'value' => ''
                                ],
                                'fax'       => [
                                    'type'  => 'input',
                                    'label' => __('fax'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        /*
                        [
                            'title'  => __("supplier's products settings"),
                            'fields' => [

                                'allow on demand'              => [
                                    'type'  => 'input',
                                    'label' => __('allow on demand'),
                                    'value' => ''
                                ],
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

                                'incoterm'      => [
                                    'type'  => 'input',
                                    'label' => __('incoterm'),
                                    'value' => ''
                                ],
                                'currency'      => [
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
                            'title'  => __('terms and conditions'),
                            'fields' => [

                                't&c'                 => [
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
                                'minimum order'                 => [
                                    'type'  => 'input',
                                    'label' => __('minimum order (EUR)'),
                                    'value' => ''
                                ],
                                'cooling period between orders' => [
                                    'type'  => 'input',
                                    'label' => __('cooling period between orders (days)'),
                                    'value' => ''
                                ],

                                'order number format'           => [
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
                        */

                        [
                            'title'  => __('currency'),
                            'fields' => [

                                'currency_id'    => [
                                    'type'  => 'currencies',
                                    'label' => __('currency'),
                                    'value' => ''
                                ],

                            ]
                        ],
                    ],
                    'route'     => [
                        'name' => 'models.supplier.store',
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

        return $this->handle($request);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inAgent(Agent $agent, ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle($request);
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            match ($routeName) {
                'procurement.marketplace.agents.show.suppliers.create' =>
                ShowMarketplaceAgent::make()->getBreadcrumbs(
                    routeParameters: $routeParameters,
                ),
                default => IndexMarketplaceSuppliers::make()->getBreadcrumbs(
                    routeName: preg_replace('/create$/', 'index', $routeName),
                    routeParameters: $routeParameters,
                ),
            },
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [


                        'label' => match ($routeName) {
                            'procurement.marketplace.agents.show.suppliers.create' => __("creating agent's supplier"),
                            default                                                => __("creating supplier")
                        }

                    ]
                ]
            ]
        );
    }
}
