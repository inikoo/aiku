<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\Marketplace\Supplier\UI;

use App\Actions\Assets\Country\UI\GetAddressData;
use App\Actions\Assets\Country\UI\GetCountriesOptions;
use App\Actions\Assets\Currency\UI\GetCurrenciesOptions;
use App\Actions\InertiaAction;
use App\Actions\Procurement\Supplier\UI\IndexSuppliers;
use App\Http\Resources\Helpers\AddressFormFieldsResource;
use App\Models\Helpers\Address;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateMarketplaceSupplier extends InertiaAction
{
    public function handle(Organisation|Agent $owner, ActionRequest $request): Response
    {
        $container = null;
        if (class_basename($owner) == 'Agent') {
            $container = [
                'icon'    => ['fal', 'fa-people-arrows'],
                'tooltip' => __('Shop'),
                'label'   => Str::possessive($owner->name)
            ];
        }

        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('new supplier'),
                'pageHead'    => [
                    'title'        => __('new supplier'),
                    'container'    => $container,
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => match ($request->route()->getName()) {
                                'grp.org.procurement.marketplace.agents.show.suppliers.create' =>
                                [
                                    'name'       => 'grp.org.procurement.marketplace.agents.show',
                                    'parameters' => array_values($request->route()->originalParameters())
                                ],
                                default => [
                                    'name'       => 'grp.org.procurement.marketplace.suppliers.index',
                                    'parameters' => array_values($request->route()->originalParameters())
                                ],
                            }
                        ]
                    ]
                ],
                'formData'    => [
                    'blueprint' => [
                        [
                            'title'  => __('ID/contact details'),
                            'icon'   => 'fal fa-address-book',
                            'fields' => [

                                'code'         => [
                                    'type'     => 'input',
                                    'label'    => __('code'),
                                    'value'    => '',
                                    'required' => true,
                                ],
                                'company_name' => [
                                    'type'  => 'input',
                                    'label' => __('company'),
                                    'value' => ''
                                ],
                                'contact_name' => [
                                    'type'  => 'input',
                                    'label' => __('contact name'),
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
                                'phone'        => [
                                    'type'    => 'phone',
                                    'label'   => __('phone'),
                                    'value'   => '',
                                    'options' => [
                                        'defaultCountry' => class_basename($owner) == 'Agent' ? $owner->getAddress()->country->code : null
                                    ]
                                ],
                                'address'      => [
                                    'type'    => 'address',
                                    'label'   => __('Address'),
                                    'value'   => AddressFormFieldsResource::make(
                                        new Address(
                                            [
                                                'country_id' => class_basename($owner) == 'Agent' ? $owner->getAddress()->country_id : app('currentTenant')->country_id,

                                            ]
                                        )
                                    )->getArray(),
                                    'options' => [
                                        'countriesAddressData' => GetAddressData::run()

                                    ]
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


                        [
                            'title'  => __('currency'),
                            'fields' => [

                                'currency_id' => [
                                    'type'  => 'currencies',
                                    'label' => __('currency'),
                                    'value' => ''
                                ],

                            ]
                        ],
                        */

                        [
                            'title'  => __('settings'),
                            'icon'   => 'fa-light fa-cog',
                            'fields' => [
                                'currency_id' => [
                                    'type'        => 'select',
                                    'label'       => __('currency'),
                                    'placeholder' => __('Select a currency'),
                                    'options'     => GetCurrenciesOptions::run(),
                                    'value'       => class_basename($owner) == 'Agent' ? $owner->currency_id : null,
                                    'required'    => true,
                                    'searchable'  => true,
                                    'mode'        => 'single'
                                ],

                                'default_product_country_origin' => [
                                    'type'        => 'select',
                                    'label'       => __("Product's country of origin"),
                                    'placeholder' => __('Select a country'),
                                    'value'       => class_basename($owner) == 'Agent' ? Arr::get($owner->data, 'default_product_country_origin') : null,
                                    'options'     => GetCountriesOptions::run(),
                                    'searchable'  => true,
                                    'mode'        => 'single'
                                ],
                            ]
                        ]


                    ],
                    'route'     =>
                        match (class_basename($owner)) {
                            'OrgAgent' => [
                                'name'       => 'grp.models.agent.supplier.store',
                                'arguments'  => $owner->slug
                            ],
                            default => [
                                'name' => 'grp.models.supplier.store',
                            ]
                        }


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

        return $this->handle(owner: app('currentTenant'), request: $request);
    }

    public function inAgent(Agent $agent, ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle(owner: $agent, request: $request);
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            match ($routeName) {
                'grp.org.procurement.marketplace.agents.show.suppliers.create' =>
                \App\Actions\SupplyChain\Agent\UI\ShowAgent::make()->getBreadcrumbs(
                    routeParameters: $routeParameters,
                ),
                default => IndexSuppliers::make()->getBreadcrumbs(
                    routeName: preg_replace('/create$/', 'index', $routeName),
                    routeParameters: $routeParameters,
                ),
            },
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [


                        'label' => match ($routeName) {
                            'grp.org.procurement.marketplace.agents.show.suppliers.create' => __("creating agent's supplier"),
                            default                                                        => __("creating supplier")
                        }

                    ]
                ]
            ]
        );
    }
}
