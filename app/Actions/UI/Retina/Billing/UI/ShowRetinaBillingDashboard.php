<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 May 2024 10:42:05 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Retina\Billing\UI;

use App\Actions\Overview\GetOrganisationOverview;
use App\Actions\Retina\Billing\IndexUnpaidInvoices;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Http\Resources\Accounting\InvoicesResource;
use App\Http\Resources\CRM\CustomersResource;
use App\Http\Resources\Helpers\CurrencyResource;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\FulfilmentCustomer;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowRetinaBillingDashboard
{
    use AsAction;


    public function asController(ActionRequest $request): Response
    {

        /** @var Customer $parent */
        $parent = $request->user()->customer;
        $currentRecurringBills = $parent->fulfilmentCustomer->recurringBills();

        $totalCurrnBill = (clone $currentRecurringBills)->whereDate('start_date', '=', now()->toDateString())->sum('total_amount') + (clone $currentRecurringBills)->whereDate('end_date', '>=', now()->toDateString())->sum('total_amount');

        // return Inertia::render('Billing/RetinaBillingDashboard', [
        //     // 'title'    => __('Billing'),
        //     'pageHead'    => [
        //         'title'         => __('Billing Dashboard'),
        //         'icon'          => [
        //             'icon'  => ['fal', 'fa-file-invoice-dollar'],
        //             'title' => __('Billing Dashboard')
        //         ],

        //     ],
        //     'dashboard_stats' => [
        //         'settings' => auth()->user()->settings,
        //         'columns' => [
        //             [
        //                 'widgets' => [
        //                     [
        //                         'data' => [
        //                             'unpaid_invoices' => InvoicesResource::collection(IndexUnpaidInvoices::run($request->user()->customer->fulfilmentCustomer))
        //                         ]
        //                     ]
        //                 ],
        //                 'widgets' => [
        //                     [
        //                         'label' => __('Total Invoices'),
        //                         'data' => $parent->stats->number_invoices
        //                     ],
        //                     [
        //                         'label' => __('Current recurring bill (amount plus close day)'),
        //                         'data' =>  $totalCurrnBill
        //                     ]
        //                 ]
        //             ],
        //         ]
        //     ],
        //     'pieData'  => $this->getDashboardData($request->user()->customer->fulfilmentCustomer),
        //     'transactionsData' => $this->getTransactionsData($request->user()->customer),
        //     'customer' => CustomersResource::make($request->user()->customer)->resolve(),
        //     'unpaid_invoices' => InvoicesResource::collection(IndexUnpaidInvoices::run($request->user()->customer->fulfilmentCustomer))
        // ]);
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
                                    'type' => 'overview_table',
                                    'data' => GetOrganisationOverview::run($parent->organisation)
                                ]
                            ]
                        ],
                        [
                            'widgets' => [
                                [
                                    'label' => __('the nutrition store'),
                                    'data' => [
                                        [
                                            'label' => __('total orders today'),
                                            'value' => 275,
                                            'type' => 'card_currency_success'
                                        ],
                                        [
                                            'label' => __('sales today'),
                                            'value' => 2345,
                                            'type' => 'card_currency'
                                        ]
                                    ],
                                    'type' => 'multi_card',
                                ],
                                [
                                    'label' => __('the yoga store'),
                                    'data' => [
                                        [
                                            'label' => __('ad spend this week'),
                                            'value' => 46,
                                            'type' => 'card_percentage'
                                        ],
                                        [
                                            'label' => __('sales today'),
                                            'value' => 2345,
                                            'type' => 'card_currency'
                                        ]
                                    ],
                                    'type' => 'multi_card',
                                ],
                                [
                                    'label' => __('ad spend this week'),
                                    'value' => 2345,
                                    'type' => 'card_currency',
                                ],
                                [
                                    'label' => __('card abandonment rate'),
                                    'value' => 45,
                                    'type' => 'card_percentage',
                                ],
                                [
                                    'label' => __('the yoga store'),
                                    'data' => [
                                        'label' => __('Total newsletter subscribers'),
                                        'value' => 55700,
                                        'progress_bar' => [
                                            'value' => 55,
                                            'max' => 100,
                                            'color' => 'success',
                                        ],
                                    ],
                                    'type' => 'card_progress_bar',
                                ],
                            ],
                        ]
                    ]
                ],
            ]
        );
    }

    public function getTransactionsData(Customer $parent): array
    {
        $stats = [];

        $stats['currency'] = [
            'currency' => CurrencyResource::make($parent->fulfilmentCustomer->fulfilment->shop->currency)->resolve()
        ];

        $stats['transactions'] = [
            'label' => __('Total Transactions'),
            'count' => $parent->fulfilmentCustomer->transactions->count(),
            'amount' => $parent->fulfilmentCustomer->transactions()->sum('net_amount')
        ];

        $stats['unpaid_bills'] = [
            'label' => __('Unpaid Bills'),
            'count' => $parent->fulfilmentCustomer->recurringBills()
                                ->whereHas('invoices', function ($query) {
                                    $query->whereNull('paid_at');
                                })
                                ->count(),
            'amount' => $parent->fulfilmentCustomer->recurringBills()
                                ->whereHas('invoices', function ($query) {
                                    $query->whereNull('paid_at');
                                })
                                ->with('invoices') // Load the related invoices
                                ->get()
                                ->sum(function ($bill) {
                                    return $bill->invoices->sum('total_amount');
                                }),
        ];

        $stats['paid_bills'] = [
            'label' => __('Paid Bills'),
            'count' => $parent->fulfilmentCustomer->recurringBills()
                            ->whereHas('invoices', function ($query) {
                                $query->whereColumn('payment_amount', '>=', 'total_amount');
                            })
                            ->count(),
            'amount' => $parent->fulfilmentCustomer->recurringBills()
                    ->whereHas('invoices', function ($query) {
                        $query->whereColumn('payment_amount', '>=', 'total_amount');
                    })
                    ->with('invoices') // Load the related invoices
                    ->get()
                    ->sum(function ($bill) {
                        return $bill->invoices->sum('total_amount');
                    }),
        ];

        return $stats;
    }

    public function getDashboardData(FulfilmentCustomer $parent): array
    {
        $stats = [];

        $stats['pallets'] = [
            'label' => __('Pallet'),
            'count' => $parent->number_pallets
        ];

        foreach (PalletStateEnum::cases() as $case) {
            $stats['pallets']['cases'][$case->value] = [
                'value' => $case->value,
                'icon'  => PalletStateEnum::stateIcon()[$case->value],
                'count' => PalletStateEnum::count($parent)[$case->value],
                'label' => PalletStateEnum::labels()[$case->value]
            ];
        }

        $stats['pallet_delivery'] = [
            'label' => __('Pallet Delivery'),
            'count' => $parent->number_pallet_deliveries
        ];
        foreach (PalletDeliveryStateEnum::cases() as $case) {
            $stats['pallet_delivery']['cases'][$case->value] = [
                'value' => $case->value,
                'icon'  => PalletDeliveryStateEnum::stateIcon()[$case->value],
                'count' => PalletDeliveryStateEnum::count($parent)[$case->value],
                'label' => PalletDeliveryStateEnum::labels()[$case->value]
            ];
        }

        $stats['pallet_return'] = [
            'label' => __('Pallet Return'),
            'count' => $parent->number_pallet_returns
        ];
        foreach (PalletReturnStateEnum::cases() as $case) {
            $stats['pallet_return']['cases'][$case->value] = [
                'value' => $case->value,
                'icon'  => PalletReturnStateEnum::stateIcon()[$case->value],
                'count' => PalletReturnStateEnum::count($parent)[$case->value],
                'label' => PalletReturnStateEnum::labels()[$case->value]
            ];
        }

        return $stats;
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
