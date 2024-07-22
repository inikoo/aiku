<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 20 Jul 2024 11:46:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Catalogue\Charge;

use App\Enums\EnumHelperTrait;

enum ChargeTypeEnum: string
{
    use EnumHelperTrait;

    case HANGING  = 'hanging';
    case PREMIUM  = 'premium';
    case TRACKING = 'tracking';
    case PASTPAY  = 'pastpay';
    case COD      = 'cod';

    public static function labels(): array
    {
        return [
            'hanging    ' => __('Hanging'),
            'premium'     => __('Premium'),
            'tracking'    => __('Tracking'),
            'cod'         => __('Charge on delivery'),
        ];
    }


}
