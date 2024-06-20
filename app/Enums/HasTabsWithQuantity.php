<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 20 Mar 2023 19:10:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums;

use App\Models\Fulfilment\PalletReturn;

trait HasTabsWithQuantity
{
    public static function navigation(PalletReturn $palletReturn): array
    {
        return collect(self::cases())->mapWithKeys(function ($case) use ($palletReturn) {
            return  [$case->value=>$case->blueprint($palletReturn)];
        })->all();
    }
}
