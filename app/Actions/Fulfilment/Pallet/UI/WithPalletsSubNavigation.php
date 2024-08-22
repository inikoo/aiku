<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 May 2024 17:55:16 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Pallet\UI;

use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;

trait WithPalletsSubNavigation
{
    protected function getPalletsInWarehouseSubNavigation(Warehouse|Location|Fulfilment $parent, $request): array
    {
        if ($parent instanceof Fulfilment) {
            return [

                [
                    'label'    => __('Current pallets'),
                    'number'   => $parent->stats->number_pallets_status_receiving +
                        $parent->stats->number_pallets_status_storing             +
                        $parent->stats->number_pallets_status_returning,
                    'href'     => [
                        'name'       => 'grp.org.fulfilments.show.operations.pallets.current.index',
                        'parameters' => $request->route()->originalParameters()
                    ],
                    'leftIcon' => [
                        'icon' => 'fal fa-warehouse-alt',
                    ]
                ],

                $parent->stats->number_pallets_state_damaged > 0 ? [
                    'label'    => __('Damaged'),
                    'align'    => 'right',
                    'number'   => $parent->stats->number_pallets_state_damaged,
                    'href'     => [
                        'name'       => 'grp.org.fulfilments.show.operations.pallets.damaged.index',
                        'parameters' => $request->route()->originalParameters()
                    ],
                    'leftIcon' => PalletStateEnum::stateIcon()[PalletStateEnum::DAMAGED->value]
                ] : [],
                $parent->stats->number_pallets_state_lost > 0 ? [
                    'label'    => __('Lost'),
                    'align'    => 'right',
                    'number'   => $parent->stats->number_pallets_state_lost,
                    'href'     => [
                        'name'       => 'grp.org.fulfilments.show.operations.pallets.lost.index',
                        'parameters' => $request->route()->originalParameters()
                    ],
                    'leftIcon' => PalletStateEnum::stateIcon()[PalletStateEnum::LOST->value]
                ] : [],
                [
                    'label'    => __('Returned'),
                    'align'    => 'right',
                    'number'   => $parent->stats->number_pallets_state_dispatched,
                    'href'     => [
                        'name'       => 'grp.org.fulfilments.show.operations.pallets.returned.index',
                        'parameters' => $request->route()->originalParameters()
                    ],
                    'leftIcon' => PalletStateEnum::stateIcon()[PalletStateEnum::DISPATCHED->value]
                ],
            ];
        }


        return [

            [
                'label'    => __('Pallets in warehouse'),
                'number'   => $parent->stats->number_pallets_status_receiving +
                    $parent->stats->number_pallets_status_storing             +
                    $parent->stats->number_pallets_status_returning,
                'href'     => [
                    'name'       => 'grp.org.warehouses.show.inventory.pallets.current.index',
                    'parameters' => $request->route()->originalParameters()
                ],
                'leftIcon' => [
                    'icon' => 'fal fa-warehouse-alt',
                ]
            ],

            $parent->stats->number_pallets_state_damaged > 0 ? [
                'label'    => __('Damaged'),
                'align'    => 'right',
                'number'   => $parent->stats->number_pallets_state_damaged,
                'href'     => [
                    'name'       => 'grp.org.warehouses.show.inventory.pallets.damaged.index',
                    'parameters' => $request->route()->originalParameters()
                ],
                'leftIcon' => PalletStateEnum::stateIcon()[PalletStateEnum::DAMAGED->value]
            ] : [],
            $parent->stats->number_pallets_state_lost > 0 ? [
                'label'    => __('Lost'),
                'align'    => 'right',
                'number'   => $parent->stats->number_pallets_state_lost,
                'href'     => [
                    'name'       => 'grp.org.warehouses.show.inventory.pallets.lost.index',
                    'parameters' => $request->route()->originalParameters()
                ],
                'leftIcon' => PalletStateEnum::stateIcon()[PalletStateEnum::LOST->value]
            ] : [],
            [
                'label'    => __('Returned'),
                'align'    => 'right',
                'number'   => $parent->stats->number_pallets_state_dispatched,
                'href'     => [
                    'name'       => 'grp.org.warehouses.show.inventory.pallets.returned.index',
                    'parameters' => $request->route()->originalParameters()
                ],
                'leftIcon' => PalletStateEnum::stateIcon()[PalletStateEnum::DISPATCHED->value]
            ],
        ];
    }
}
