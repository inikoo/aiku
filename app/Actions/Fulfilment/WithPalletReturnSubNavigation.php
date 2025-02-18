<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 May 2024 12:26:53 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment;

use App\Models\Fulfilment\Fulfilment;
use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\ActionRequest;

trait WithPalletReturnSubNavigation
{
    public function getPalletReturnSubNavigation(Fulfilment|Warehouse $parent, ActionRequest $request): array
    {
        $subNavigation = [];

        $subNavigation[] = [
            'isAnchor' => true,
            'route' => [
                'name' => match (class_basename($parent)) {
                    'Fulfilment' => 'grp.org.fulfilments.show.operations.pallet-returns.confirmed.index',
                    'Warehouse' => 'grp.org.warehouses.show.dispatching.pallet-returns.confirmed.index'
                },
                'parameters' => $request->route()->originalParameters()

            ],

            'label' => __('New'),
            'leftIcon' => [
                'icon' => 'fal fa-stream',
                'tooltip' => __('New'),
            ],
            'number' => $parent->stats->number_pallets_state_confirmed
        ];

        $subNavigation[] = [
            'route' => [
                'name' => match (class_basename($parent)) {
                    'Fulfilment' => 'grp.org.fulfilments.show.operations.pallet-returns.picking.index',
                    'Warehouse' => 'grp.org.warehouses.show.dispatching.pallet-returns.picking.index'
                },
                'parameters' => $request->route()->originalParameters()
            ],

            'label' => __("Picking"),
            'leftIcon' => [
                'icon' => 'fal fa-parking',
                'tooltip' => __("Picking"),
            ],
            'number' => $parent->stats->number_pallet_returns_state_picking
        ];

        $subNavigation[] = [
            'route' => [
                'name' => match (class_basename($parent)) {
                    'Fulfilment' => 'grp.org.fulfilments.show.operations.pallet-returns.picked.index',
                    'Warehouse' => 'grp.org.warehouses.show.dispatching.pallet-returns.picked.index'
                },
                'parameters' => $request->route()->originalParameters()
            ],

            'label' => __("Picked"),
            'leftIcon' => [
                'icon' => 'fal fa-parking',
                'tooltip' => __("Picked"),
            ],
            'number' => $parent->stats->number_pallet_returns_state_picked
        ];

        $subNavigation[] = [
            'route' => [
                'name' => match (class_basename($parent)) {
                    'Fulfilment' => 'grp.org.fulfilments.show.operations.pallet-returns.index',
                    'Warehouse' => 'grp.org.warehouses.show.dispatching.pallet-returns.index'
                },
                'parameters' => $request->route()->originalParameters()
            ],

            'label' => __("All"),
            'leftIcon' => [
                'icon' => 'fal fa-parking',
                'tooltip' => __("All"),
            ],
            'number' => $parent->stats->number_pallet_returns
        ];

        return $subNavigation;
    }
}
