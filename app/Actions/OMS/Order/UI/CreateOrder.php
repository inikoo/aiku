<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\Order\UI;

use App\Actions\Assets\Country\UI\GetAddressData;
use App\Actions\InertiaAction;
use App\Http\Resources\Helpers\AddressFormFieldsResource;
use App\Http\Resources\Helpers\AddressResource;
use App\Models\CRM\Customer;
use App\Models\Helpers\Address;
use App\Models\Market\Shop;
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
                    $request->route()->originalParameters()
                ),
                'title'       => __('new order'),
                'pageHead'    => [
                    'title'        => __('new order'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => match ($request->route()->getName()) {
                                'shops.show.orders.create'    => 'shops.show.orders.index',
                                default                       => preg_replace('/create$/', 'index', $request->route()->getName())
                            },
                            'parameters' => array_values($request->route()->originalParameters())
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
                                        'placeholder' => __('Select a customer'),
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
                        'name'     => 'grp.models.shop.order.store',
                        'arguments'=> [$shop->slug]
                    ]
                ]

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('shops.orders.edit');
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
