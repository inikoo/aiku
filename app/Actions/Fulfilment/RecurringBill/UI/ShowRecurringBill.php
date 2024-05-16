<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 16:54:01 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill\UI;

use App\Actions\Fulfilment\FulfilmentCustomer\ShowFulfilmentCustomer;
use App\Actions\Fulfilment\StoredItem\UI\GetStoredItemShowcase;
use App\Actions\Fulfilment\StoredItem\UI\IndexStoredItemPallets;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\StoredItem\StoredItemStatusEnum;
use App\Enums\UI\Fulfilment\RecurringBillTabsEnum;
use App\Enums\UI\Fulfilment\StoredItemTabsEnum;
use App\Http\Resources\Fulfilment\PalletsResource;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Http\Resources\History\HistoryResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
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

    public function asController(Organisation $organisation, Warehouse $warehouse, Fulfilment $fulfilment, StoredItem $storedItem, ActionRequest $request): StoredItem
    {
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(RecurringBillTabsEnum::values());

        return $this->handle($storedItem);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, StoredItem $storedItem, ActionRequest $request): StoredItem
    {
        $this->initialisationFromFulfilment($fulfilment, $request)->withTab(RecurringBillTabsEnum::values());

        return $this->handle($storedItem);
    }

    public function handle(StoredItem $storedItem): StoredItem
    {
        return $storedItem;
    }

    public function htmlResponse(StoredItem $storedItem, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Fulfilment/RecurringBill',
            [
                'title'       => __('recurring bill'),
                'breadcrumbs' => $this->getBreadcrumbs($storedItem),
                'pageHead'    => [
                    'icon'          =>
                        [
                            'icon'  => ['fa', 'fa-narwhal'],
                            'title' => __('recurring bill')
                        ],
                    'title'  => $storedItem->slug,
                    'actions'=> [
                        [
                            'type'    => 'button',
                            'style'   => 'exit',
                            'tooltip' => __('return to customer'),
                            'label'   => $storedItem->status == StoredItemStatusEnum::RETURNED ? __('returned') : __('return to customer'),
                            'route'   => [
                                'name'       => 'grp.fulfilment.stored-items.setReturn',
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                            'disabled' => $storedItem->status == StoredItemStatusEnum::RETURNED
                        ],
                        [
                            'type'    => 'button',
                            'style'   => 'negative',
                            'icon'    => 'fal fa-fragile',
                            'tooltip' => __('Set as damaged'),
                            'label'   => $storedItem->status == StoredItemStatusEnum::DAMAGED ? __('damaged') : __('set as damaged'),
                            'route'   => [
                                'name'       => 'grp.fulfilment.stored-items.setDamaged',
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                            'disabled' => $storedItem->status == StoredItemStatusEnum::DAMAGED
                        ],
                        [
                            'type'    => 'button',
                            'style'   => 'secondary',
                            'icon'    => 'fal fa-pencil',
                            'tooltip' => __('Edit recurring bill'),
                            'label'   => __('recurring bills'),
                            'route'   => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ],
                    ],
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => StoredItemTabsEnum::navigation(),
                ],

                'palletRoute' => [
                    'index' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallets.index',
                        'parameters' => [
                            'organisation'         => $request->route('organisation'),
                            'fulfilment'           => $request->route('fulfilment'),
                            'fulfilmentCustomer'   => $request->route('fulfilmentCustomer')
                        ]
                    ],
                ],

                'locationRoute' => [
                    'index' => [
                        'name'       => 'grp.org.fulfilments.show.crm.customers.show.pallets.locations.index',
                        'parameters' => [
                            'organisation'         => $request->route('organisation'),
                            'fulfilment'           => $request->route('fulfilment'),
                            'fulfilmentCustomer'   => $request->route('fulfilmentCustomer')
                        ]
                    ],
                ],

                'update' => [
                    'name'       => 'grp.models.stored-items.move',
                    'parameters' => [
                        'storedItem'         => $storedItem->id
                    ]
                ],

                StoredItemTabsEnum::SHOWCASE->value => $this->tab == StoredItemTabsEnum::SHOWCASE->value ?
                    fn () => GetStoredItemShowcase::run($storedItem)
                    : Inertia::lazy(fn () => GetStoredItemShowcase::run($storedItem)),

                StoredItemTabsEnum::PALLETS->value => $this->tab == StoredItemTabsEnum::PALLETS->value ?
                    fn () => PalletsResource::collection(IndexStoredItemPallets::run($storedItem))
                    : Inertia::lazy(fn () => PalletsResource::collection(IndexStoredItemPallets::run($storedItem))),

                StoredItemTabsEnum::HISTORY->value => $this->tab == StoredItemTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($storedItem))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($storedItem)))

            ]
        )->table(IndexHistory::make()->tableStructure('hst'))
            ->table(IndexStoredItemPallets::make()->tableStructure($storedItem, 'pallets'));
    }


    public function jsonResponse(StoredItem $storedItem): StoredItemResource
    {
        return new StoredItemResource($storedItem);
    }

    public function getBreadcrumbs(StoredItem $storedItem, $suffix = null): array
    {

        return array_merge(
            ShowFulfilmentCustomer::make()->getBreadcrumbs(request()->route()->originalParameters()),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'grp.fulfilment.stored-items.index',
                            ],
                            'label' => __('recurring bills')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.fulfilment.stored-items.show',
                                'parameters' => array_values(request()->route()->originalParameters())
                            ],
                            'label' => $storedItem->slug,
                        ],
                    ],
                    'suffix' => $suffix,
                ],
            ]
        );
    }
}
