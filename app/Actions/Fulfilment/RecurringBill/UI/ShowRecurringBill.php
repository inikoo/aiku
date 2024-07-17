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
use App\Enums\UI\Fulfilment\RecurringBillTabsEnum;
use App\Enums\UI\Fulfilment\StoredItemTabsEnum;
use App\Http\Resources\Catalogue\ServicesResource;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Http\Resources\Fulfilment\PhysicalGoodsResource;
use App\Http\Resources\Fulfilment\RecurringBillResource;
use App\Http\Resources\History\HistoryResource;
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
                                'quantity'      => $recurringBill->number_pallets ?? 0,
                                'price_base'    => __('Multiple'),
                                'price_total'   => 11111111
                            ],
                            [
                                'label'         => __('Services'),
                                'quantity'      => $recurringBill->stats->number_services ?? 0,
                                'price_base'    => __('Multiple'),
                                'price_total'   => 1111111
                            ],
                            [
                                'label'         => __('Physical Goods'),
                                'quantity'      => $recurringBill->stats->number_physical_goods ?? 0,
                                'price_base'    => __('Multiple'),
                                'price_total'   => 1111111
                            ],
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

                StoredItemTabsEnum::SHOWCASE->value => $this->tab == StoredItemTabsEnum::SHOWCASE->value ?
                    fn () => RecurringBillResource::make($recurringBill)
                    : Inertia::lazy(fn () => RecurringBillResource::make($recurringBill)),

                // Todo @kirin fix this below
                RecurringBillTabsEnum::PALLETS->value => $this->tab == RecurringBillTabsEnum::PALLETS->value ?
                    fn () => PalletsResource::collection(IndexPallets::run($recurringBill))
                    : Inertia::lazy(fn () => PalletsResource::collection(IndexPallets::run($recurringBill))),

                // Todo @kirin fix this below
                RecurringBillTabsEnum::SERVICES->value => $this->tab == RecurringBillTabsEnum::PALLETS->value ?
                    fn () => ServicesResource::collection(IndexFulfilmentServices::run($recurringBill))
                    : Inertia::lazy(fn () => ServicesResource::collection(IndexFulfilmentServices::run($recurringBill))),

                // Todo @kirin fix this below
                RecurringBillTabsEnum::PHYSICAL_GOODS->value => $this->tab == RecurringBillTabsEnum::PALLETS->value ?
                    fn () => PhysicalGoodsResource::collection(IndexFulfilmentPhysicalGoods::run($recurringBill))
                    : Inertia::lazy(fn () => PhysicalGoodsResource::collection(IndexFulfilmentPhysicalGoods::run($recurringBill))),

                // Todo @kirin fix this below
                RecurringBillTabsEnum::HISTORY->value => $this->tab == RecurringBillTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($recurringBill))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($recurringBill)))

            ]
            // Todo @kirin please fix this below
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
