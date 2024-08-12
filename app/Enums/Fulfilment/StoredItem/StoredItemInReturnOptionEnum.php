<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 03:02:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\StoredItem;

use App\Enums\EnumHelperTrait;

enum StoredItemInReturnOptionEnum: string
{
    use EnumHelperTrait;

    case ALL         = 'all';
    case SELECTED    = 'selected';


    public static function labels(): array
    {
        return [
            'all'         => __('All'),
            'selected'    => __('Selected')
        ];
    }

    public static function count(): array
    {
        return [
            'all'      => 0,
            'selected' => 0
        ];
    }
}
