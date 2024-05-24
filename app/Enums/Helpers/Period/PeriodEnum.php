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

    case TODAY = 'today';
    case WEEK  = 'week';
    case MONTH = 'month';
    case YEAR  = 'year';

    public static function labels(): array
    {
        return [
            'today' => __('Today'),
            'week'  => __('Week'),
            'month' => __('Month'),
            'year'  => __('Year')
        ];
    }

    public static function date(): array
    {
        return [
            'today' => null,
            'week'  => now()->format('Ymd'),
            'month' => now()->format('Ym'),
            'year'  => now()->format('Y')
        ];
    }
}
