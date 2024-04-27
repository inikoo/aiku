<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 08:53:56 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\Rental;

use App\Enums\EnumHelperTrait;

enum RentalUnitEnum: string
{
    use EnumHelperTrait;

    case DAY   = 'day';
    case WEEK  = 'week';
    case MONTH = 'month';

    public static function labels(): array
    {
        return [
            'day'   => __('day'),
            'week'  => __('week'),
            'month' => __('month'),
        ];
    }

    public static function abbreviations(): array
    {
        return [
            'day'   => 'd',
            'week'  => 'w',
            'month' => 'm'
        ];
    }


}
