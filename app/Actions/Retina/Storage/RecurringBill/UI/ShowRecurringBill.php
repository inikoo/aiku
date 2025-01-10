<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 16:54:01 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Storage\RecurringBill\UI;

use App\Actions\Helpers\History\UI\IndexHistory;
use App\Actions\RetinaAction;
use App\Actions\UI\Retina\Billing\UI\ShowRetinaBillingDashboard;
use App\Enums\UI\Fulfilment\RecurringBillTabsEnum;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Http\Resources\Fulfilment\RecurringBillResource;
use App\Http\Resources\Fulfilment\RecurringBillTransactionsResource;
use App\Http\Resources\History\HistoryResource;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\StoredItem;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property StoredItem $storedItem
 */
class ShowRecurringBill extends RetinaAction
{
    public function asController(RecurringBill $recurringBill, ActionRequest $request): RecurringBill
    {
        $this->initialisation($request)->withTab(RecurringBillTabsEnum::values());

        return $this->handle($recurringBill);
    }

    public function handle(RecurringBill $recurringBill): RecurringBill
    {
        return $recurringBill;
    }

    public function htmlResponse(RecurringBill $recurringBill, ActionRequest $request): Response
    {
        $palletPriceTotal = 0;
        foreach ($recurringBill->transactions()->where('item_type', 'Pallet') as $transaction) {
            $palletPriceTotal += $transaction->item->rental->price;
        }

        $showGrossAndDiscount = $recurringBill->gross_amount !== $recurringBill->net_amount;
        return Inertia::render(
            'Billing/RetinaRecurringBill',
            [
                'title'       => __('recurring bill'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'icon'          =>
                        [
                            'icon'  => ['fa', 'fa-receipt'],
                            'title' => __('recurring bill')
                        ],
                    'model'        => __('Recurring Bill'),
                    'title'        => $recurringBill->slug
                ],
                'timeline_rb'   => [
                    'start_date' => $recurringBill->start_date,
                    'end_date'   => $recurringBill->end_date
                ],
                'status_rb'        => $recurringBill->status,
                'box_stats'        => [
                    'customer'      => FulfilmentCustomerResource::make($recurringBill->fulfilmentCustomer),
                    'stats'         => [
                        'number_pallets'         => $recurringBill->stats->number_transactions_type_pallets,
                        'number_stored_items'    => $recurringBill->stats->number_transactions_type_stored_items,
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
                                'label'         => __('Pallets'),
                                'price_base'    => __('Multiple'),
                                'price_total'   => $recurringBill->rental_amount
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
                                'label'         => __('Gross'),
                                'information'   => '',
                                'price_total'   => $recurringBill->gross_amount
                            ],
                            [
                                'label'         => __('Discounts'),
                                'information'   => '',
                                'price_total'   => $recurringBill->discount_amount
                            ],
                        ] : [],
                        $showGrossAndDiscount ? [
                            [
                                'label'         => __('Net'),
                                'information'   => '',
                                'price_total'   => $recurringBill->net_amount
                            ],
                            [
                                'label'         => __('Tax').' '.$recurringBill->taxCategory->rate * 100 . '%',
                                'information'   => '',
                                'price_total'   => $recurringBill->tax_amount
                            ],
                        ] : [
                            [
                                'label'         => __('Net'),
                                'information'   => '',
                                'price_total'   => $recurringBill->net_amount
                            ],
                            [
                                'label'         => __('Tax').' '.$recurringBill->taxCategory->rate * 100 . '%',
                                'information'   => '',
                                'price_total'   => $recurringBill->tax_amount
                            ],
                        ],
                        [
                            [
                                'label'         => __('Total'),
                                'price_total'   => $recurringBill->total_amount
                            ],
                        ],
                    ],
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => RecurringBillTabsEnum::navigation(),
                ],

                RecurringBillTabsEnum::TRANSACTIONS->value => $this->tab == RecurringBillTabsEnum::TRANSACTIONS->value ?
                fn () => RecurringBillTransactionsResource::collection(IndexRecurringBillTransactions::run($recurringBill, RecurringBillTabsEnum::TRANSACTIONS->value))
                : Inertia::lazy(fn () => RecurringBillTransactionsResource::collection(IndexRecurringBillTransactions::run($recurringBill, RecurringBillTabsEnum::TRANSACTIONS->value))),

                // RecurringBillTabsEnum::HISTORY->value => $this->tab == RecurringBillTabsEnum::HISTORY->value ?
                // fn () => HistoryResource::collection(IndexHistory::run($recurringBill))
                // : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($recurringBill)))
            ]
        )->table(
            IndexRecurringBillTransactions::make()->tableStructure(
                $recurringBill,
                prefix: RecurringBillTabsEnum::TRANSACTIONS->value
            )
        );
        // ->table(IndexHistory::make()->tableStructure(prefix: RecurringBillTabsEnum::HISTORY->value));
        //            ->table(IndexFulfilmentServices::make()->tableStructure($recurringBill, prefix: RecurringBillTabsEnum::SERVICES->value))
        //            ->table(IndexFulfilmentPhysicalGoods::make()->tableStructure($recurringBill, prefix: RecurringBillTabsEnum::PHYSICAL_GOODS->value))
        //            ->table(IndexRetinaPallets::make()->tableStructure($recurringBill, RecurringBillTabsEnum::PALLETS->value));
    }


    public function jsonResponse(RecurringBill $recurringBill): RecurringBillResource
    {
        return new RecurringBillResource($recurringBill);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, $suffix = ''): array
    {
        $headCrumb = function (RecurringBill $recurringBill, array $routeParameters, string $suffix) {
            return [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('Recurring bills')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $recurringBill->slug,
                        ],

                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        $recurringBill = RecurringBill::where('slug', $routeParameters['recurringBill'])->first();


        return match ($routeName) {
            'retina.billing.recurring.show' => array_merge(
                ShowRetinaBillingDashboard::make()->getBreadcrumbs(),
                $headCrumb(
                    $recurringBill,
                    [
                        'index' => [
                            'name'       => 'retina.billing.recurring.index',
                            'parameters' => []
                        ],
                        'model' => [
                            'name'       => 'retina.billing.recurring.show',
                            'parameters' => [$recurringBill->slug]
                        ]
                    ],
                    $suffix
                ),
            ),

            default => []
        };
    }
}
