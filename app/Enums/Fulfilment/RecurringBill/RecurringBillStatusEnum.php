<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 May 2023 21:14:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\RecurringBill;

use App\Enums\EnumHelperTrait;

enum RecurringBillStatusEnum: string
{
    use EnumHelperTrait;

    case CURRENT    = 'current';
    case PAST       = 'past';


    public static function labels($forElements = false): array
    {
        $labels = [
            'current'    => __('Current bill'),
            'past'       => __('Previous bill'),

        ];


        return $labels;
    }

}
