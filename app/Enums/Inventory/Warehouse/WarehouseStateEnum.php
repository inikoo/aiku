<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 12 Jan 2024 17:05:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Inventory\Warehouse;

use App\Enums\EnumHelperTrait;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;

enum WarehouseStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS   = 'in-process';
    case OPEN         = 'open';
    case CLOSING_DOWN = 'closing-down';
    case CLOSED       = 'closed';

    public static function labels(): array
    {
        return [
            'in-process'      => __('In Process'),
            'open'            => __('Open'),
            'closing-down'    => __('Closing Down'),
            'closed'          => __('Closed')
        ];
    }

    public static function count(Organisation|Group $parent): array
    {
        $stats = $parent->inventoryStats;

        return [
            'in-process'      => $stats->number_warehouses_state_in_process,
            'open'            => $stats->number_warehouses_state_open,
            'closing-down'    => $stats->number_warehouses_state_closing_down,
            'closed'          => $stats->number_warehouses_state_closed
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'in-process'   => [
                'tooltip' => __('In Process'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-green-500 animate-pulse'
            ],
            'open' => [
                'tooltip' => __('Open'),
                'icon'    => 'fal fa-check',
                'class'   => ''
            ],
            'closing-down' => [
                'tooltip' => __('Closing Down'),
                'icon'    => 'fal fa-do-not-enter',
                'class'   => ''
            ],
            'closed'    => [
                'tooltip' => __('Closed'),
                'icon'    => 'fal fa-times-hexagon',
                'class'   => 'text-red-500'
            ]
        ];
    }
}
