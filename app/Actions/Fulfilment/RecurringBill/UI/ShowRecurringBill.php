<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 16:54:01 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill\UI;

use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentPhysicalGoods;
use App\Actions\Fulfilment\Fulfilment\UI\IndexFulfilmentServices;
use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\UI\IndexPallets;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\OrgAction;
use App\Enums\UI\Fulfilment\RecurringBillTabsEnum;
use App\Enums\UI\Fulfilment\StoredItemTabsEnum;
use App\Http\Resources\Catalogue\ServicesResource;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Http\Resources\Fulfilment\PhysicalGoodsResource;
use App\Http\Resources\Fulfilment\RecurringBillResource;
use App\Http\Resources\History\HistoryResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\StoredItem;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
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

    public function asController(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, RecurringBill $recurringBill, ActionRequest $request): RecurringBill
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
                'breadcrumbs' => $this->getBreadcrumbs($recurringBill),
                'pageHead'    => [
                    'icon'          =>
                        [
                            'icon'  => ['fa', 'fa-narwhal'],
                            'title' => __('recurring bill')
                        ],
                    'title'  => $recurringBill->slug
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => RecurringBillTabsEnum::navigation(),
                ],

                StoredItemTabsEnum::SHOWCASE->value => $this->tab == StoredItemTabsEnum::SHOWCASE->value ?
                    fn () => RecurringBillResource::make($recurringBill)
                    : Inertia::lazy(fn () => RecurringBillResource::make($recurringBill)),

                RecurringBillTabsEnum::PALLETS->value => $this->tab == RecurringBillTabsEnum::PALLETS->value ?
                    fn () => PalletsResource::collection(IndexPallets::run($recurringBill))
                    : Inertia::lazy(fn () => PalletsResource::collection(IndexPallets::run($recurringBill))),

                RecurringBillTabsEnum::SERVICES->value => $this->tab == RecurringBillTabsEnum::PALLETS->value ?
                    fn () => ServicesResource::collection(IndexFulfilmentServices::run($recurringBill))
                    : Inertia::lazy(fn () => ServicesResource::collection(IndexFulfilmentServices::run($recurringBill))),

                RecurringBillTabsEnum::PHYSICAL_GOODS->value => $this->tab == RecurringBillTabsEnum::PALLETS->value ?
                    fn () => PhysicalGoodsResource::collection(IndexFulfilmentPhysicalGoods::run($recurringBill))
                    : Inertia::lazy(fn () => PhysicalGoodsResource::collection(IndexFulfilmentPhysicalGoods::run($recurringBill))),

                RecurringBillTabsEnum::HISTORY->value => $this->tab == RecurringBillTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($recurringBill))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($recurringBill)))

            ]
            // Todo @kirin or @raul please fix this below
        )->table(IndexHistory::make()->tableStructure(prefix: RecurringBillTabsEnum::HISTORY->value));
        //            ->table(IndexFulfilmentServices::make()->tableStructure($recurringBill, prefix: RecurringBillTabsEnum::SERVICES->value))
        //            ->table(IndexFulfilmentPhysicalGoods::make()->tableStructure($recurringBill, prefix: RecurringBillTabsEnum::PHYSICAL_GOODS->value))
        //            ->table(IndexPallets::make()->tableStructure($recurringBill, RecurringBillTabsEnum::PALLETS->value));
    }


    public function jsonResponse(RecurringBill $recurringBill): RecurringBillResource
    {
        return new RecurringBillResource($recurringBill);
    }

    public function getBreadcrumbs(RecurringBill $recurringBill, $suffix = null): array
    {

        return array_merge(
            ShowFulfilmentCustomer::make()->getBreadcrumbs(request()->route()->originalParameters()),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.show.recurring_bills.index',
                                'parameters' => [
                                    array_values(request()->route()->originalParameters())
                                ]
                            ],
                            'label' => __('recurring bills')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.org.fulfilments.show.crm.customers.show.recurring_bills.show',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => $recurringBill->slug,
                        ],
                    ],
                    'suffix' => $suffix,
                ],
            ]
        );
    }
}
