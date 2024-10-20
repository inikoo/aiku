<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 21:30:14 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Catalogue\Subscription;

use App\Enums\Catalogue\IsBillableState;
use App\Enums\EnumHelperTrait;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;

enum SubscriptionStateEnum: string
{
    use EnumHelperTrait;
    use IsBillableState;


    case IN_PROCESS        = 'in-process';
    case ACTIVE            = 'active';
    case DISCONTINUED      = 'discontinued';

    public static function count(Shop|Organisation|Group $parent): array
    {
        $stats = match (class_basename($parent)) {
            'Shop' => $parent->stats,
            'Organisation', 'Group' => $parent->catalogueStats,
        };


        return [
            'in-process'   => $stats->number_subscriptions_state_in_process,
            'active'       => $stats->number_subscriptions_state_active,
            'discontinued' => $stats->number_subscriptions_state_discontinued
        ];
    }
}
