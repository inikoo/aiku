<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Apr 2024 12:45:19 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Discounts\OfferCampaign;

use App\Enums\EnumHelperTrait;
use App\Models\Catalogue\Shop;

enum OfferCampaignStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS = 'in-process';
    case ACTIVE     = 'active';
    case FINISHED   = 'finished';

    public static function labels(Shop $shop): array
    {
        $labels = [
            'in-process'   => __('In process'),
            'active'       => __('Active'),
            'finished'     => __('Finished'),
        ];

        return $labels;
    }
}
