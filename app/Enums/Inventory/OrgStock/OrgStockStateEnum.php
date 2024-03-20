<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 16:26:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Inventory\OrgStock;

use App\Enums\EnumHelperTrait;
use App\Models\SysAdmin\Organisation;

enum OrgStockStateEnum: string
{
    use EnumHelperTrait;

    case ACTIVE            = 'active';
    case DISCONTINUING     = 'discontinuing';
    case DISCONTINUED      = 'discontinued';
    case SUSPENDED         = 'suspended';


    public static function labels(): array
    {
        return [
            'active'        => __('Active'),
            'discontinuing' => __('Discontinuing'),
            'discontinued'  => __('Discontinued'),
            'suspended'     => __('Suspended')
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'active'    => [
                'tooltip' => __('contacted'),
                'icon'    => 'fal fa-chair',
                'class'   => 'text-green-500'
            ],
            'discontinuing'         => [
                'tooltip' => __('discontinuing'),
                'icon'    => 'fal fa-thumbs-down',
                'class'   => 'text-gray-500'
            ],
            'discontinued'      => [
                'tooltip' => __('discontinued'),
                'icon'    => 'fal fa-laugh',
                'class'   => 'text-red-500'
            ],
            'suspended'      => [
                'tooltip' => __('suspended'),
                'icon'    => 'fal fa-pause',
                'class'   => 'text-yellow-500'
            ],
        ];
    }

    public static function count(Organisation $parent): array
    {
        $stats = $parent->inventoryStats;

        return [
            'active'            => $stats->number_stocks_state_active,
            'discontinuing'     => $stats->number_stocks_state_discontinuing,
            'discontinued'      => $stats->number_stocks_state_discontinued,
            'suspended'         => $stats->number_stocks_state_suspended
        ];
    }
}
