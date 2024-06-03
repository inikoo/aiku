<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 02:16:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\SupplyChain\Stock;

use App\Enums\EnumHelperTrait;
use App\Models\SysAdmin\Group;

enum StockStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS        = 'in-process';
    case ACTIVE            = 'active';
    case DISCONTINUING     = 'discontinuing';
    case DISCONTINUED      = 'discontinued';
    case SUSPENDED         = 'suspended';

    public static function labels(): array
    {
        return [
            'in-process'    => __('In process'),
            'active'        => __('Active'),
            'discontinuing' => __('Discontinuing'),
            'discontinued'  => __('Discontinued'),
            'suspended'     => __('Suspended')
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'in-process' => [
                'tooltip' => __('in process'),
                'icon'    => 'fal fa-seedling',
                'class'   => 'text-indigo-500'
            ],
            'active'    => [
                'tooltip' => __('contacted'),
                'icon'    => 'fal fa-chair',
                'class'   => 'text-green-500'
            ],
            'discontinuing' => [
                'tooltip' => __('discontinuing'),
                'icon'    => 'fal fa-exclamation-triangle',
                'class'   => 'text-orange-500'
            ],
            'discontinued'      => [
                'tooltip' => __('discontinued'),
                'icon'    => 'fal fa-laugh',
                'class'   => 'text-red-500'
            ],
            'suspended'      => [
                'tooltip' => __('suspended'),
                'icon'    => 'fas fa-pause-circle',
                'class'   => 'text-slate-300'
            ],
        ];
    }

    public static function count(Group $parent): array
    {
        $stats = $parent->inventoryStats;

        return [
            'in-process'        => $stats->number_stocks_state_in_process,
            'active'            => $stats->number_stocks_state_active,
            'discontinuing'     => $stats->number_stocks_state_discontinuing,
            'discontinued'      => $stats->number_stocks_state_discontinued,
            'suspended'         => $stats->number_stocks_state_suspended
        ];
    }

}
