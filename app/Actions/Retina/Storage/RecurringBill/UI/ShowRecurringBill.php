<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 16:54:01 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Storage\RecurringBill\UI;

use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentPhysicalGoods;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentServices;
use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\RetinaAction;
use App\Actions\UI\Retina\Billing\UI\ShowBillingDashboard;
use App\Enums\UI\Fulfilment\RecurringBillTabsEnum;
use App\Enums\UI\Fulfilment\StoredItemTabsEnum;
use App\Http\Resources\Catalogue\ServicesResource;
use App\Http\Resources\Fulfilment\FulfilmentCustomerResource;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Http\Resources\Fulfilment\PhysicalGoodsResource;
use App\Http\Resources\Fulfilment\RecurringBillResource;
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
                'status'        => $recurringBill->status,
                'box_stats'     => [
                    'customer'      => FulfilmentCustomerResource::make($recurringBill->fulfilmentCustomer),
                    'stats'         => $recurringBill->stats,
                    'order_summary' => [
                        [
                            [
                                "label"         => __("total"),
                                'price_gross'   => $recurringBill->gross_amount,
                                'price_net'     => $recurringBill->net_amount,
                                "price_total"   => $recurringBill->total_amount,
                                // "information" => 777777,
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
                ShowBillingDashboard::make()->getBreadcrumbs(),
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
