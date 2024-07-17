<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 May 2024 12:26:53 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment;

use App\Models\Fulfilment\FulfilmentCustomer;
use Lorisleiva\Actions\ActionRequest;

trait WithFulfilmentCustomerSubNavigation
{
    public function getFulfilmentCustomerSubNavigation(FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): array
    {
        $subNavigation = [];

        $subNavigation[]=[
            'href' => [
                'name'      => 'grp.org.fulfilments.show.crm.customers.show',
                'parameters'=> $request->route()->originalParameters()

            ],

            'label'     => $fulfilmentCustomer->customer->name,
            'leftIcon'  => [
                'icon'    => 'fal fa-stream',
                'tooltip' => __('customer'),
            ],


        ];

        if($fulfilmentCustomer->pallets_storage && $fulfilmentCustomer->rentalAgreement()->exists()) {
            $subNavigation[]=[
                'href' => [
                    'name'      => 'grp.org.fulfilments.show.crm.customers.show.web-users.index',
                    'parameters'=> $request->route()->originalParameters()

                ],

                'label'     => __('Web users'),
                'leftIcon'  => [
                    'icon'    => 'fal fa-terminal',
                    'tooltip' => __('Web users'),
                ],
                'number'=> $fulfilmentCustomer->customer->stats->number_web_users

            ];
            $subNavigation[]=[
                'href' => [
                    'name'      => 'grp.org.fulfilments.show.crm.customers.show.pallets.index',
                    'parameters'=> $request->route()->originalParameters()

                ],

                'label'     => __('Pallets'),
                'leftIcon'  => [
                    'icon'    => 'fal fa-pallet',
                    'tooltip' => __('Pallets'),
                ],
                'number'=> $fulfilmentCustomer->number_pallets

            ];

            $subNavigation[]=[
                'href' => [
                    'name'      => 'grp.org.fulfilments.show.crm.customers.show.pallet_deliveries.index',
                    'parameters'=> $request->route()->originalParameters()
                ],

                'label'     => __('Deliveries'),
                'leftIcon'  => [
                    'icon'    => 'fal fa-truck-couch',
                    'tooltip' => __('Pallet deliveries'),
                ],
                'number'=> $fulfilmentCustomer->number_pallet_deliveries

            ];

            $subNavigation[]=[
                'href' => [
                    'name'      => 'grp.org.fulfilments.show.crm.customers.show.recurring_bills.index',
                    'parameters'=> $request->route()->originalParameters()
                ],

                'label'     => __('Recurring bills'),
                'leftIcon'  => [
                    'icon'    => 'fal fa-receipt',
                    'tooltip' => __('Recurring bills'),
                ],
                'number'=> $fulfilmentCustomer->number_recurring_bills

            ];
        }

        if($fulfilmentCustomer->items_storage) {

            $subNavigation[]=[
                'href' => [
                    'name'      => 'grp.org.fulfilments.show.crm.customers.show.stored-items.index',
                    'parameters'=> $request->route()->originalParameters()
                ],

                'label'     => __('Stored Items'),
                'leftIcon'  => [
                    'icon'    => 'fal fa-narwhal',
                    'tooltip' => __('Stored Items'),
                ],
                'number'=> $fulfilmentCustomer->number_stored_items

            ];

        }

        if (($fulfilmentCustomer->pallets_storage || $fulfilmentCustomer->dropshipping) && $fulfilmentCustomer->palletDeliveries()->exists()) {


            $subNavigation[]=[
                'href' => [
                    'name'      => 'grp.org.fulfilments.show.crm.customers.show.pallet_returns.index',
                    'parameters'=> $request->route()->originalParameters()
                ],

                'label'     => __('Returns'),
                'leftIcon'  => [
                    'icon'    => 'fal fa-sign-out-alt',
                    'tooltip' => __('Pallet returns'),
                ],
                'number'=> $fulfilmentCustomer->number_pallet_returns

            ];

        }

        if($fulfilmentCustomer->dropshipping) {

            $subNavigation[]=[
                'href' => [
                    'name'      => 'grp.org.fulfilments.show.crm.customers.show.invoices.index',
                    'parameters'=> $request->route()->originalParameters()
                ],

                'label'     => __('Invoices'),
                'leftIcon'  => [
                    'icon'    => 'fal fa-file-invoice-dollar',
                    'tooltip' => __('Invoices'),
                ],
                'number'=> $fulfilmentCustomer->customer->stats->number_invoices

            ];
        }


        return $subNavigation;
    }
}
