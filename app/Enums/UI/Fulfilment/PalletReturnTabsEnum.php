<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 15:17:32 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Fulfilment;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabsWithQuantity;
use App\Models\Fulfilment\PalletReturn;

enum PalletReturnTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabsWithQuantity;

    case PALLETS      = 'pallets';
    case STORED_ITEMS = 'stored_items';

    case SERVICES       = 'services';
    case PHYSICAL_GOODS = 'physical_goods';


    case HISTORY = 'history';

    public function blueprint(PalletReturn $parent): array
    {
        return match ($this) {
            PalletReturnTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            PalletReturnTabsEnum::PALLETS => [
                'title' => __("pallets ($parent->number_pallets)"),
                'icon'  => 'fal fa-pallet',
            ],
            PalletReturnTabsEnum::STORED_ITEMS => [
                'title' => __("stored items ($parent->number_stored_items)"),
                'icon'  => 'fal fa-cube',
            ],
            PalletReturnTabsEnum::SERVICES => [
                'title' => __("services ({$parent->stats->number_services})"),
                'icon'  => 'fal fa-concierge-bell',
            ],
            PalletReturnTabsEnum::PHYSICAL_GOODS => [
                'title' => __("physical goods ({$parent->stats->number_physical_goods})"),
                'icon'  => 'fal fa-cube',
            ],
        };
    }
}
