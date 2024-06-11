<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 06 Jun 2024 14:05:15 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\UI;

use App\Models\CRM\Customer;

trait WithCustomerSubNavigation
{
    protected function getCustomerSubNavigation(Customer $customer): array
    {
        return [
            [
                'label'    => $customer->name,
                'href'     => [
                    'name'       => 'grp.org.shops.show.crm.customers.show',
                    'parameters' => [$this->organisation->slug, $customer->shop->slug, $customer->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-user'],
                    'tooltip' => __('Customer')
                ]
            ],
            [
                'label'    => __('Clients'),
                'number'   => $customer->stats->number_clients,
                'href'     => [
                    'name'       => 'grp.org.shops.show.crm.customers.show.customer-clients.index',
                    'parameters' => [$this->organisation->slug, $customer->shop->slug, $customer->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-folder'],
                    'tooltip' => __('clients')
                ]
            ],
            [
                'label'    => __('Portofolios'),
                // 'number'   => $customer->dropshippingCustomerPortfolios->count(),
                // 'href'     => [
                //     'name'       => 'grp.org.shops.show.catalogue.departments.show.products.index',
                //     'parameters' => [$this->organisation->slug, $department->shop->slug, $department->slug]
                // ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-folder'],
                    'tooltip' => __('portofolios')
                ]
            ],
        ];
    }

}
