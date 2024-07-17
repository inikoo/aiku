<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 16:54:01 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill\UI;

use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentPhysicalGoods;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentServices;
use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\Retina\Storage\RecurringBill\UI\IndexRecurringBillTransactions;
use App\Enums\UI\Fulfilment\RecurringBillTabsEnum;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Http\Resources\Fulfilment\RecurringBillResource;
use App\Http\Resources\Fulfilment\RecurringBillTransactionsResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\StoredItem;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property StoredItem $storedItem
 */
class ShowRecurringBill extends OrgAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, RecurringBill $recurringBill, ActionRequest $request): RecurringBill
    {
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(RecurringBillTabsEnum::values());

        return $this->handle($recurringBill);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, RecurringBill $recurringBill, ActionRequest $request): RecurringBill
    {
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(RecurringBillTabsEnum::values());

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
        // dd(RecurringBillTransactionsResource::collection(IndexRecurringBillTransactions::run($recurringBill, RecurringBillTabsEnum::TRANSACTIONS->value)));
        return Inertia::render(
            'Org/Fulfilment/RecurringBill',
            [
                'title'       => __('recurring bill'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'icon'          =>
                        [
                            'icon'  => ['fa', 'fa-narwhal'],
                            'title' => __('recurring bill')
                        ],
                    'model'        => __('Recurring Bill'),
                    'title'        => $recurringBill->slug
                ],

                'timeline_rb'   => [
                    'start_date' => $recurringBill->start_date,
                    'end_date'   => $recurringBill->end_date
                ],
                'consolidateRoute'  => [
                    'name'          => null,
                    'parameters'    => null,
                    'method'        => null
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
                                'quantity'      => $recurringBill->stats->number_transactions_type_pallets ?? 0,
                                'price_base'    => __('Multiple'),
                                'price_total'   => $palletPriceTotal ?? 0
                            ],
                            // [
                            //     'label'         => __('Stored Items'),
                            //     'quantity'      => $recurringBill->stats->number_transactions_type_stored_items ?? 0,
                            //     'price_base'    => __('Multiple'),
                            //     'price_total'   => 1111111
                            // ],
                        ],
                        [
                            [
                                'label'         => __('Shipping'),
                                'information'   => __('Shipping fee to your address using DHL service.'),
                                'price_total'   => 1111
                            ],
                            [
                                'label'         => __('Tax'),
                                'information'   => __('Tax is based on 10% of total order.'),
                                'price_total'   => 1111111
                            ],
                        ],
                        [
                            [
                                'label'         => __('Total'),
                                'price_total'   => 222222222
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
            ]
            // Todo @kirin please fix this below
        )->table(
            IndexRecurringBillTransactions::make()->tableStructure(
                $recurringBill,
                prefix: RecurringBillTabsEnum::TRANSACTIONS->value
            )
        )->table(IndexHistory::make()->tableStructure(prefix: RecurringBillTabsEnum::HISTORY->value));
        //            ->table(IndexFulfilmentServices::make()->tableStructure($recurringBill, prefix: RecurringBillTabsEnum::SERVICES->value))
        //            ->table(IndexFulfilmentPhysicalGoods::make()->tableStructure($recurringBill, prefix: RecurringBillTabsEnum::PHYSICAL_GOODS->value))
        //            ->table(IndexPallets::make()->tableStructure($recurringBill, RecurringBillTabsEnum::PALLETS->value));
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
                            'label' => $recurringBill->reference,
                        ],

                    ],
                    'suffix' => $suffix
                ],
            ];
        };

        $recurringBill = RecurringBill::where('slug', $routeParameters['recurringBill'])->first();

        return match ($routeName) {
            'grp.org.fulfilments.show.crm.customers.show.recurring_bills.show' => array_merge(
                ShowFulfilmentCustomer::make()->getBreadcrumbs(Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])),
                $headCrumb(
                    $recurringBill,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.recurring_bills.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.crm.customers.show.recurring_bills.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'fulfilmentCustomer', 'recurringBill'])
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.fulfilments.show.operations.recurring_bills.show' => array_merge(
                ShowFulfilment::make()->getBreadcrumbs(Arr::only($routeParameters, ['organisation', 'fulfilment'])),
                $headCrumb(
                    $recurringBill,
                    [
                        'index' => [
                            'name'       => 'grp.org.fulfilments.show.operations.recurring_bills.index',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'recurringBill'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.fulfilments.show.operations.recurring_bills.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'fulfilment', 'recurringBill'])
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }
}
