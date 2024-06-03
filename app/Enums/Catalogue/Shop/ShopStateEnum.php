<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 00:47:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Catalogue\Shop;

use App\Enums\EnumHelperTrait;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;

enum ShopStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS   = 'in-process';
    case OPEN         = 'open';
    case CLOSING_DOWN = 'closing-down';
    case CLOSED       = 'closed';

    public static function labels($forElements = false): array
    {
        return [
            'in-process'      => __('In Process'),
            'open'            => __('Open'),
            'closing-down'    => __('Closing Down'),
            'closed'          => __('Closed')
        ];
    }

    public static function count(
        Organisation|Group $parent,
        $forElements = false
    ): array {
        $stats = $parent->catalogueStats;

        return [
            'in-process'      => $stats->number_shops_state_in_process,
            'open'            => $stats->number_shops_state_open,
            'closing-down'    => $stats->number_shops_state_closing_down,
            'closed'          => $stats->number_shops_state_closed
        ];
    }
}
