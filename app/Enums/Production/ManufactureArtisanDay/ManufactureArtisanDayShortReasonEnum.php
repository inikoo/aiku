<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 16:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Production\ManufactureArtisanDay;

use App\Enums\EnumHelperTrait;

enum ManufactureArtisanDayShortReasonEnum: string
{
    use EnumHelperTrait;

    case LEFT_EARLY            = 'left_early';
    case ARRIVED_LATE          = 'arrived_late';
    case SICK                  = 'sick';
    case WAITING_FOR_MATERIALS = 'waiting_for_materials';
    case APPOINTMENT           = 'appointment';
    case OTHER                 = 'other';

    public static function labels($forElements = false): array
    {
        return [
            'left_early'            => __('Left early'),
            'arrived_late'          => __('Arrived late'),
            'sick'                  => __('Sick'),
            'waiting_for_materials' => __('Waiting for materials'),
            'appointment'           => __('Appointment'),
            'other'                 => __('Other'),
        ];
    }
}
