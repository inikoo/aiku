<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Sales\Order\UI;

use App\Actions\InertiaAction;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

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
                                    'number' => [
                                        'type'  => 'input',
                                        'label' => __('number')
                                    ],
                                    'customer_number' => [
                                        'type'  => 'input',
                                        'label' => __('customer number')
                                    ],
                                ]
                            ]
                        ],
                    'route'     => [
                        'name'     => 'models.shop.customer.store',
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
