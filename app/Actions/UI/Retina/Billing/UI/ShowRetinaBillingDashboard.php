<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 May 2024 10:42:05 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Retina\Billing\UI;

use App\Actions\Overview\GetOrganisationOverview;
use App\Models\CRM\Customer;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowRetinaBillingDashboard
{
    use AsAction;


    public function asController(ActionRequest $request): Response
    {

        /** @var Customer $customer */
        $customer = $request->user()->customer;
        $currentRecurringBill = $customer->fulfilmentCustomer->currentRecurringBill;

        return Inertia::render(
            'Billing/OverviewHub',
            [
                // 'breadcrumbs' => $this->getBreadcrumbs(
                //     $routeName,
                //     $routeParameters
                // ),
                'title'       => __('overview'),
                'pageHead'    => [
                    'icon'      => [
                        'icon'  => ['fal', 'fa-mountains'],
                        'title' => __('overview')
                    ],
                    'title'     => __('overview'),
                ],
                'dashboard' => [
                    'settings' => auth()->user()->settings,
                    'columns' => [
                        [
                            'widgets' => [
                                [
                                    'type' => 'overview_table',// unpaid_invoices
                                    'data' => GetOrganisationOverview::run($customer->organisation)
                                ]
                            ]
                        ],
                        [
                            'widgets' => [
                               $currentRecurringBill ?
                                [
                                    'label' => __('Next bill'),
                                    'data' => [
                                        [
                                            'label' => __('current total'),
                                            'value' => $currentRecurringBill->total_amount,//<-- need to be currency
                                            'type' => 'card_currency_success'
                                        ],
                                        [
                                            'label' => __('To be invoiced at'),
                                            'value' => $currentRecurringBill->end_date,// a date
                                            'type' => 'card_currency'
                                        ]
                                    ],
                                    'type' => 'multi_card',
                                ] : null,

                                [
                                    'label' => __('Unpaid Invoices'),
                                    'value' => 0,//$customer->stats->number_unpaid_invoices,
                                    'type' => 'card_currency', // change to card_number_attention
                                ],
                                [
                                    'label' => __('Total Invoices'),
                                    'value' => $customer->stats->number_invoices,
                                    'type' => 'card_percentage',// change to card_number
                                ],

                            ],
                        ]
                    ]
                ],
            ]
        );
    }





    public function getBreadcrumbs($label = null): array
    {
        return [
            [

                'type'   => 'simple',
                'simple' => [
                    'icon'  => 'fal fa-home',
                    'label' => $label,
                    'route' => [
                        'name' => 'retina.dashboard.show'
                    ]
                ]

            ],

        ];
    }
}
