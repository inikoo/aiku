<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 Feb 2023 22:52:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait EnumHelperTrait
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function valuesDB(): array
    {
        return Arr::map(
            array_column(self::cases(), 'value'),
            function (string $value) {
                return Str::kebab($value);
            }
        );
    }
}
