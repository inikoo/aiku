<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 29 Jun 2023 13:51:08 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Web\Website;

use App\Enums\EnumHelperTrait;

enum WebsiteEngineEnum: string
{
    use EnumHelperTrait;

    case AURORA = 'aurora';
    case IRIS   = 'iris';
    case OTHER  = 'other';


    public static function labels(): array
    {
        return [
            'aurora' => 'Aurora',
            'iris'   => 'Iris',
            'other'  => __('Other'),
        ];
    }

    public static function count(): array
    {
        $webStats = app('currentTenant')->webStats;

        return [
            'aurora' => $webStats->number_websites_engine_aurora,
            'iris'   => $webStats->number_websites_engine_iris,
            'other'  => $webStats->number_websites_engine_other,
        ];
    }


}
