<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 21 Oct 2026 16:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Production\ManufactureArtisanDay;

use App\Enums\EnumHelperTrait;

enum ManufactureArtisanDayResolutionEnum: string
{
    use EnumHelperTrait;

    case APPROVED     = 'approved';
    case NOT_APPROVED = 'not_approved';
    case REASON_GIVEN = 'reason_given';
    case CHECKED      = 'checked';

    public static function labels($forElements = false): array
    {
        return [
            'approved'     => __('Overtime approved'),
            'not_approved' => __('Overtime not authorised'),
            'reason_given' => __('Reason given'),
            'checked'      => __('Break checked'),
        ];
    }

    public function answers(ManufactureArtisanDayStatusEnum $status): bool
    {
        return match ($this) {
            self::APPROVED, self::NOT_APPROVED => $status == ManufactureArtisanDayStatusEnum::OVER,
            self::REASON_GIVEN                 => $status == ManufactureArtisanDayStatusEnum::UNDER,
            self::CHECKED                      => $status == ManufactureArtisanDayStatusEnum::NO_BREAK,
        };
    }
}
