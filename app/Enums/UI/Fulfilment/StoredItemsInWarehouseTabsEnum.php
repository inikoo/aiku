<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 22 Aug 2024 10:25:01 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Fulfilment;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum StoredItemsInWarehouseTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case STORED_ITEMS    = 'stored_items';

    case PALLET_STORED_ITEMS        = 'pallet_stored_items';


    public function blueprint(): array
    {
        return match ($this) {
            StoredItemsInWarehouseTabsEnum::STORED_ITEMS => [
                'title' => __('Stored Items'),
                'icon'  => 'fal fa-narwhal',
            ],

            StoredItemsInWarehouseTabsEnum::PALLET_STORED_ITEMS => [

                'title' => __('Pallet/Stored items'),
                'icon'  => 'fal fa-pallet',
            ],

        };
    }
}
