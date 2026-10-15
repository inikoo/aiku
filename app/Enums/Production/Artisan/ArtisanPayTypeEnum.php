<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 12:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\Production\Artisan;

use App\Enums\EnumHelperTrait;

enum ArtisanPayTypeEnum: string
{
    use EnumHelperTrait;

    case PIECE_RATE = 'piece_rate';
    case HOURLY     = 'hourly';

    public static function labels($forElements = false): array
    {
        return [
            'piece_rate' => __('Piece rate'),
            'hourly'     => __('Hourly'),
        ];
    }
}
