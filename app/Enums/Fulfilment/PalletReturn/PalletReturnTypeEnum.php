<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jan 2024 15:25:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Fulfilment\PalletReturn;

use App\Enums\EnumHelperTrait;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;

enum PalletReturnTypeEnum: string
{
    use EnumHelperTrait;

    case PALLET   = 'pallet';
    case STORED_ITEM    = 'stored_item';

    public static function labels($forElements = false): array
    {
        return [
            'pallet'   => __('Pallet'),
            'stored_item'    => __('Stored Item'),
        ];
    }

    public static function stateIcon(): array
    {
        return [
            'pallet' => [
                'tooltip' => __('Pallet'),
                'icon'    => 'fal fa-pallet',
            ],
            'stored_item'  => [
                'tooltip' => __('Stored Item'),
                'icon'    => 'fal fa-narwhal',
            ],

        ];
    }
}
