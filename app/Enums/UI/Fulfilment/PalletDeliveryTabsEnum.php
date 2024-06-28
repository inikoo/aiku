<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 15:17:32 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Fulfilment;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;
use App\Enums\HasTabsWithQuantity;
use App\Models\Fulfilment\PalletDelivery;

enum PalletDeliveryTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabsWithQuantity;

    case PALLETS        = 'pallets';
    case SERVICES       = 'services';
    case PHYSICAL_GOODS = 'physical_goods';


    case HISTORY = 'history';

    public function blueprint(PalletDelivery $parent): array
    {
        $stats = $parent->stats;
        return match ($this) {
            PalletDeliveryTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            PalletDeliveryTabsEnum::PALLETS => [
                'title' => __("pallets ($stats->number_pallets)"),
                'icon'  => 'fal fa-pallet',
            ],
            PalletDeliveryTabsEnum::SERVICES => [
                'title' => __("services ($stats->number_services)"),
                'icon'  => 'fal fa-concierge-bell',
            ],
            PalletDeliveryTabsEnum::PHYSICAL_GOODS => [
                'title' => __("physical goods ($stats->number_physical_goods)"),
                'icon'  => 'fal fa-cube',
            ],
        };
    }
}
