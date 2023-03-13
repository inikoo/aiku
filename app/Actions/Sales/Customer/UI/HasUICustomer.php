<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:52:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Customer\UI;

use App\Actions\Marketing\Shop\ShowShop;
use App\Models\Sales\Customer;

trait HasUICustomer
{
    public function getBreadcrumbs(string $routeName, Customer $customer): array
    {
        $headCrumb = function (array $routeParameters = []) use ($customer, $routeName) {
            $indexRouteParameters = $routeParameters;
            array_pop($indexRouteParameters);

            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'name'            => $customer->reference,
                    'index'           => [
                        'route'           => preg_replace('/(show|edit)$/', 'index', $routeName),
                        'routeParameters' => $indexRouteParameters,
                        'overlay'         => __('customers list')
                    ],
                    'modelLabel'      => [
                        'label' => __('customer')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'customers.show', 'customers.edit' => $headCrumb([$customer->shop->slug]),
            'shops.show.customers.show',
            'shops.show.customers.edit'
             => array_merge(
                 (new ShowShop())->getBreadcrumbs($customer->shop),
                 $headCrumb([$customer->shop->slug, $customer->slug])
             ),
            default => []
        };
    }
}
