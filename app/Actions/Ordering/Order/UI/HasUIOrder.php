<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order\UI;

use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\Catalogue\Shop\UI\ShowShop;

trait HasUIOrder
{
    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $order = $routeParameters['order'];


        $headCrumb = function (array $parameters = []) use ($order, $routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $parameters,
                    'name'            => $order->reference,
                    'index'           =>
                        match ($routeName) {
                            'shops.show.customers.show.orders.show', 'customers.show.orders.show' => null,

                            default => [
                                'route'           => preg_replace('/(show|edit)$/', 'index', $routeName),
                                'routeParameters' => function () use ($parameters) {
                                    $indexParameters = $parameters;
                                    array_pop($indexParameters);

                                    return $indexParameters;
                                },
                                'overlay'         => __('order list')
                            ],
                        },


                    'modelLabel' => [
                        'label' => __('order')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'shops.show.customers.show.orders.show' => array_merge(
                ShowCustomer::make()->getBreadcrumbs('shops.show.customers.show', $routeParameters['customer']),
                $headCrumb([$routeParameters['shop']->slug, $routeParameters['customer']->slug, $routeParameters['order']->slug])
            ),
            'customers.show.orders.show' => array_merge(
                \App\Actions\CRM\Customer\UI\ShowCustomer::make()->getBreadcrumbs('customers.show', $routeParameters['customer']),
                $headCrumb([$routeParameters['customer']->slug, $routeParameters['order']->slug])
            ),
            'shops.show.orders.show' => array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters['shop']),
                $headCrumb([$routeParameters['shop']->slug, $routeParameters['order']->slug])
            ),
            'orders.show' => $headCrumb([$routeParameters['order']->slug])
        };
    }
}
