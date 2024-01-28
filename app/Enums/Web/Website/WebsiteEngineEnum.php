<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 29 Jun 2023 13:51:08 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Web\Website;

use App\Enums\EnumHelperTrait;
use App\Models\SysAdmin\Organisation;

enum WebsiteEngineEnum: string
{
    use EnumHelperTrait;

    case AURORA = 'aurora';
    case AIKU   = 'aiku';
    case OTHER  = 'other';


    public static function labels(): array
    {
        return [
            'aurora' => 'Aurora',
            'aiku'   => 'Aiku',
            'other'  => __('Other'),
        ];
    }

    public static function count(Organisation $organisation): array
    {
        $webStats = $organisation->webStats;

        return [
            'aurora' => $webStats->number_websites_engine_aurora,
            'aiku'   => $webStats->number_websites_engine_aiku,
            'other'  => $webStats->number_websites_engine_other,
        ];
    }


}
