<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 Feb 2023 22:52:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums;

use Illuminate\Support\Arr;

trait EnumHelperTrait
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function asDatabaseColumns(): array
    {
        return Arr::map(
            array_column(self::cases(), 'value'),
            function (string $value) {
                return str_replace('-', '_', $value);
            }
        );
    }
}
