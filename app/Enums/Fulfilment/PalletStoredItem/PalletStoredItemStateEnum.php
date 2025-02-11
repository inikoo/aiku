<?php
/*
 * author Arya Permana - Kirin
 * created on 11-02-2025-08h-55m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Enums\Fulfilment\Pallet;

use App\Enums\EnumHelperTrait;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;

enum PalletStoredItemStateEnum: string
{
    use EnumHelperTrait;

    case IN_PROCESS = 'in_process';
    case ACTIVE = 'active';
    case HISTORIC = 'historic';


    public static function labels(): array
    {
        return [
            'in_process'                => __('In process'),
            'active'                    => __('Active'),
            'historic'                  => __('Historic'),
        ];
    }
}
