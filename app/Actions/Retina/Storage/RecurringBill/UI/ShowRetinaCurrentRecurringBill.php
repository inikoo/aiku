<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 16:54:01 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Storage\RecurringBill\UI;

use App\Actions\RetinaAction;
use App\Actions\UI\Retina\Billing\UI\ShowRetinaBillingDashboard;
use App\Enums\UI\Fulfilment\RecurringBillTabsEnum;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Http\Resources\Fulfilment\RecurringBillResource;
use App\Http\Resources\Fulfilment\RecurringBillTransactionsResource;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\StoredItem;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property StoredItem $storedItem
 */
class ShowRetinaCurrentRecurringBill extends RetinaAction
{
    public function asController(RecurringBill $recurringBill, ActionRequest $request): RecurringBill
    {
        $this->initialisation($request)->withTab(RecurringBillTabsEnum::values());
        $currentRecurringBill = $this->customer->fulfilmentCustomer->currentRecurringBill;

        return $this->handle($currentRecurringBill);
    }

    public function handle(RecurringBill $recurringBill): RecurringBill
    {
        return $recurringBill;
    }

    public function htmlResponse(RecurringBill $recurringBill, ActionRequest $request): Response
    {
        $navigation = RecurringBillTabsEnum::navigation();
        unset($navigation[RecurringBillTabsEnum::HISTORY->value]);

        $palletPriceTotal = 0;
        foreach ($recurringBill->transactions()->where('item_type', 'Pallet') as $transaction) {
            $palletPriceTotal += $transaction->item->rental->price;
        }

        $showGrossAndDiscount = $recurringBill->gross_amount !== $recurringBill->net_amount;

        return Inertia::render(
            'Billing/RetinaRecurringBill',
            [
                'title'       => __('recurring bill'),
                'breadcrumbs' => $this->getBreadcrumbs(),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fa', 'fa-receipt'],
                            'title' => __('recurring bill')
                        ],
                    'model' => __(' Bill'),
                    'title' => $recurringBill->slug
                ],
                'timeline_rb' => [
                    'start_date' => $recurringBill->start_date,
                    'end_date'   => $recurringBill->end_date
                ],
                'status_rb'   => $recurringBill->status,
                'box_stats'   => [
                    'customer'      => FulfilmentCustomerResource::make($recurringBill->fulfilmentCustomer),
                    'stats'         => [
                        'number_pallets'      => $recurringBill->stats->number_transactions_type_pallets,
                        'number_stored_items' => $recurringBill->stats->number_transactions_type_stored_items,
                    ],
                    'order_summary' => [
                        // [
                        //     [
                        //         "label"         => __("total"),
                        //         'price_gross'   => $recurringBill->gross_amount,
                        //         'price_net'     => $recurringBill->net_amount,
                        //         "price_total"   => $recurringBill->total_amount,
                        //         // "information" => 777777,
                        //     ],
                        // ],
                        [
                            [
                                'label'       => __('Pallets'),
                                'price_base'  => __('Multiple'),
                                'price_total' => $recurringBill->rental_amount
                            ],
                            [
                                'label'       => __('Services'),
                                'price_base'  => __('Multiple'),
                                'price_total' => $recurringBill->services_amount
                            ],
                            [
                                'label'       => __('Products'),
                                'price_base'  => __('Multiple'),
                                'price_total' => $recurringBill->goods_amount
                            ],
                            // [
                            //     'label'         => __('Stored Items'),
                            //     'quantity'      => $recurringBill->stats->number_transactions_type_stored_items ?? 0,
                            //     'price_base'    => __('Multiple'),
                            //     'price_total'   => 1111111
                            // ],
                        ],
                        $showGrossAndDiscount ? [
                            [
                                'label'       => __('Gross'),
                                'information' => '',
                                'price_total' => $recurringBill->gross_amount
                            ],
                            [
                                'label'       => __('Discounts'),
                                'information' => '',
                                'price_total' => $recurringBill->discount_amount
                            ],
                        ] : [],
                        $showGrossAndDiscount
                            ? [
                            [
                                'label'       => __('Net'),
                                'information' => '',
                                'price_total' => $recurringBill->net_amount
                            ],
                            [
                                'label'       => __('Tax').' '.$recurringBill->taxCategory->rate * 100 .'%',
                                'information' => '',
                                'price_total' => $recurringBill->tax_amount
                            ],
                        ]
                            : [
                            [
                                'label'       => __('Net'),
                                'information' => '',
                                'price_total' => $recurringBill->net_amount
                            ],
                            [
                                'label'       => __('Tax').' '.$recurringBill->taxCategory->rate * 100 .'%',
                                'information' => '',
                                'price_total' => $recurringBill->tax_amount
                            ],
                        ],
                        [
                            [
                                'label'       => __('Total'),
                                'price_total' => $recurringBill->total_amount
                            ],
                        ],
                    ],
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => $navigation,
                ],

                RecurringBillTabsEnum::TRANSACTIONS->value => $this->tab == RecurringBillTabsEnum::TRANSACTIONS->value ?
                    fn () => RecurringBillTransactionsResource::collection(IndexRetinaRecurringBillTransactions::run($recurringBill, RecurringBillTabsEnum::TRANSACTIONS->value))
                    : Inertia::lazy(fn () => RecurringBillTransactionsResource::collection(IndexRetinaRecurringBillTransactions::run($recurringBill, RecurringBillTabsEnum::TRANSACTIONS->value))),

            ]
        )->table(
            IndexRetinaRecurringBillTransactions::make()->tableStructure(
                $recurringBill,
                prefix: RecurringBillTabsEnum::TRANSACTIONS->value
            )
        );
    }


    public function jsonResponse(RecurringBill $recurringBill): RecurringBillResource
    {
        return new RecurringBillResource($recurringBill);
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            ShowRetinaBillingDashboard::make()->getBreadcrumbs(),
            [

                'type'   => 'simple',
                'simple' => [
                    'icon'  => 'fal fa-receipt',
                    'label' => __('next bill'),
                    'route' => [
                        'name' => 'retina.billing.next_recurring_bill'
                    ]
                ]

            ],
        );
    }
}
