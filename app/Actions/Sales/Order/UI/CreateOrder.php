<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Sales\Order\UI;

use App\Actions\Assets\Country\UI\GetAddressData;
use App\Actions\InertiaAction;
use App\Http\Resources\Helpers\AddressFormFieldsResource;
use App\Http\Resources\Helpers\AddressResource;
use App\Models\Helpers\Address;
use App\Models\Market\Shop;
use App\Models\Sales\Customer;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class CreateOrder extends InertiaAction
{
    public function handle(Shop $shop, ActionRequest $request): Response
    {

        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('new order'),
                'pageHead'    => [
                    'title'        => __('new order'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => match ($this->routeName) {
                                'shops.show.orders.create'    => 'shops.show.orders.index',
                                default                       => preg_replace('/create$/', 'index', $this->routeName)
                            },
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],
                'formData'    => [
                    'blueprint' =>
                        [
                            [
                                'title'  => __('number'),
                                'fields' => [
                                    'date' => [
                                        'type' => 'date',
                                        'label'=> __('date')
                                    ],
                                    'number' => [
                                        'type'  => 'input',
                                        'label' => __('number')
                                    ],
                                ]
                            ],
                            [
                                'title'  => __('customer'),
                                'fields' => [
                                    'customer_id' => [
                                        'type'        => 'select',
                                        'label'       => 'name',
                                        'placeholder' => 'Select A Customer',
                                        'options'     => Options::forModels(Customer::query()->where('shop_id', $shop->id)),

                                    ],
                                    'customer_number' => [
                                        'type'  => 'input',
                                        'label' => __('customer number')
                                    ],
                                ]
                            ],
                            [
                                'title'  => __('billing address'),
                                'fields' => [
                                    'billing_address'      => [
                                        'type'    => 'address',
                                        'label'   => __('Address'),
                                        'value'   => AddressFormFieldsResource::make(
                                            new Address(
                                                [
                                                    'country_id' => $shop->country_id,

                                                ]
                                            )
                                        )->getArray(),
                                        'options' => [
                                            'countriesAddressData' => GetAddressData::run()

                                        ]
                                    ]
                                ]
                            ],
                            [
                                'title'  => __('Delivery address'),
                                'fields' => [
                                    'delivery_address' => [
                                        'type'    => 'address',
                                        'label'   => __('Address'),
                                        'value'   => AddressResource::make(
                                            new Address(
                                                [
                                                    'country_id' => $shop->country_id,

                                                ]
                                            )
                                        )->getArray(),
                                        'options' => [
                                            'countriesAddressData' => GetAddressData::run()

                                        ]
                                    ]
                                ]
                            ]
                        ],
                    'route'     => [
                        'name'     => 'models.shop.order.store',
                        'arguments'=> [$shop->slug]
                    ]
                ]

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('shops.orders.edit');
    }


    public function asController(Shop $shop, ActionRequest $request): Response
    {
        $this->initialisation($request);
        return $this->handle($shop, $request);
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            IndexOrders::make()->getBreadcrumbs(
                routeName: preg_replace('/create$/', 'index', $routeName),
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'         => 'creatingModel',
                    'creatingModel'=> [
                        'label'=> __('creating order'),
                    ]
                ]
            ]
        );
    }
}
