<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Sales\Order\UI;

use App\Actions\Marketing\Shop\ShowShop;
use App\Actions\Sales\Customer\UI\ShowCustomer;

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
                    'name'            => $order->number,
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
                ShowCustomer::make()->getBreadcrumbs('customers.show', $routeParameters['customer']),
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
