<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\StoredItem\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Inventory\InventoryDashboard;
use App\Enums\UI\StoredItemTabsEnum;
use App\Enums\UI\WarehouseTabsEnum;
use App\Http\Resources\Fulfilment\StoredItemResource;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Models\Fulfilment\StoredItem;
use App\Models\Inventory\Warehouse;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property StoredItem $storedItem
 */
class ShowStoredItem extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('fulfilment.edit');

        return $request->user()->hasPermissionTo("fulfilment.view");
    }

    public function asController(StoredItem $storedItem, ActionRequest $request): void
    {
        $this->initialisation($request)->withTab(StoredItemTabsEnum::values());
        $this->storedItem = $storedItem;
    }


    public function htmlResponse(): Response
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
                    'title' => $this->storedItem->slug,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => StoredItemTabsEnum::navigation(),
                ],
            ]
        );
    }


    public function jsonResponse(): StoredItemResource
    {
        return new StoredItemResource($this->storedItem);
    }

    public function getBreadcrumbs(StoredItem $storedItem, $suffix = null): array
    {
        return array_merge(
            (new InventoryDashboard())->getBreadcrumbs(),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'fulfilment.stored-items.index',
                            ],
                            'label' => __('stored items')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'fulfilment.stored-items.show',
                                'parameters' => [$storedItem->slug]
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
