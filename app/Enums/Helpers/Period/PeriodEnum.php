<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 08 Jun 2023 23:19:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Helpers\Period;

use App\Enums\EnumHelperTrait;

enum PeriodEnum: string
{
    use EnumHelperTrait;

    case Day = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case QUARTER = 'quarter';
    case YEAR = 'year';

    public static function labels(): array
    {
        return [
            'day'     => __('Day'),
            'week'    => __('Week'),
            'month'   => __('Month'),
            'quarter' => __('Quarter'),
            'year'    => __('Year')
        ];
    }

    public static function date(): array
    {
        return [
            'day'     => null,
            'week'    => now()->format('Ymd'),
            'month'   => now()->format('Ym'),
            'quarter' => now()->format('Ymd'),
            'year'    => now()->format('Y')
        ];
    }
}
