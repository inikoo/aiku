<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 20:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Production\ManufactureTaskSession;

use App\Enums\EnumHelperTrait;

enum ManufactureTaskSessionTimeEditReasonEnum: string
{
    use EnumHelperTrait;

    case FORGOT_TO_START     = 'forgot_to_start';
    case FORGOT_TO_STOP      = 'forgot_to_stop';
    case LEFT_OPEN_OVERNIGHT = 'left_open_overnight';
    case OTHER               = 'other';
    case CLOSED_AT_SHIFT_END = 'closed_at_shift_end';

    public static function labels($forElements = false): array
    {
        return [
            'forgot_to_start'     => __('Forgot to start'),
            'forgot_to_stop'      => __('Forgot to stop'),
            'left_open_overnight' => __('Left open overnight'),
            'other'               => __('Other'),
            'closed_at_shift_end' => __('Closed at the end of the shift'),
        ];
    }

    /**
     * @return array<int, self>
     */
    public static function supervisorReasons(): array
    {
        return [self::FORGOT_TO_START, self::FORGOT_TO_STOP, self::LEFT_OPEN_OVERNIGHT, self::OTHER];
    }
}
