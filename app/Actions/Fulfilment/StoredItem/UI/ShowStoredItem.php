<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\UI;

use App\Actions\Helpers\History\IndexHistory;
use App\Actions\InertiaAction;
use App\Enums\Fulfilment\StoredItem\StoredItemStatusEnum;
use App\Enums\UI\StoredItemTabsEnum;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Http\Resources\History\HistoryResource;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\StoredItem;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property StoredItem $storedItem
 */
class ShowStoredItem extends InertiaAction
{
    public Customer|null $customer = null;

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('fulfilment.edit');

        return $request->user()->hasPermissionTo("fulfilment.view");
    }

    public function asController(Customer $customer, StoredItem $storedItem, ActionRequest $request): void
    {
        $this->customer = $customer;
        $this->initialisation($request)->withTab(StoredItemTabsEnum::values());
        $this->storedItem = $storedItem;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'Fulfilment/StoredItem',
            [
                'title'       => __('stored item'),
                'breadcrumbs' => $this->getBreadcrumbs($this->storedItem),
                'pageHead'    => [
                    'icon'          =>
                        [
                            'icon'  => ['fa', 'fa-narwhal'],
                            'title' => __('stored item')
                        ],
                    'title'  => $this->storedItem->slug,
                    'actions'=> [
                        [
                            'type'    => 'button',
                            'style'   => 'cancel',
                            'tooltip' => __('return to customer'),
                            'label'   => __($this->storedItem->status == StoredItemStatusEnum::RETURNED ? 'returned' : 'return to customer'),
                            'route'   => [
                                'name'       => 'grp.fulfilment.stored-items.setReturn',
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                            'disabled' => $this->storedItem->status == StoredItemStatusEnum::RETURNED
                        ],
                        [
                            'type'    => 'button',
                            'style'   => 'edit',
                            'tooltip' => __('edit stored items'),
                            'label'   => __('stored items'),
                            'route'   => [
                                'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ],
                        [
                            'type'    => 'button',
                            'style'   => 'delete',
                            'tooltip' => __('set as damaged'),
                            'label'   => __($this->storedItem->status == StoredItemStatusEnum::DAMAGED ? 'damaged' : 'set as damaged'),
                            'route'   => [
                                'name'       => 'grp.fulfilment.stored-items.setDamaged',
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                            'disabled' => $this->storedItem->status == StoredItemStatusEnum::DAMAGED
                        ],
                    ],
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => StoredItemTabsEnum::navigation(),
                ],

                StoredItemTabsEnum::HISTORY->value => $this->tab == StoredItemTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($this->storedItem))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($this->storedItem)))

            ]
        )->table(IndexHistory::make()->tableStructure());
    }


    public function jsonResponse(): StoredItemResource
    {
        return new StoredItemResource($this->storedItem);
    }

    public function getBreadcrumbs(StoredItem $storedItem, $suffix = null): array
    {
        return array_merge(
            ShowFulfilment::make()->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'grp.fulfilment.stored-items.index',
                            ],
                            'label' => __('stored items')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'grp.fulfilment.stored-items.show',
                                'parameters' => array_values($request->route()->originalParameters())
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
