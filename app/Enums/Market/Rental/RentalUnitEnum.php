<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Apr 2024 10:20:39 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Market\Rental;

use App\Enums\EnumHelperTrait;

enum RentalUnitEnum: string
{
    use EnumHelperTrait;

    case DAY = 'day';
    case WEEK = 'week';
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
