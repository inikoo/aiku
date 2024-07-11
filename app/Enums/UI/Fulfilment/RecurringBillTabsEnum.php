<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 17:01:01 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Fulfilment;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum RecurringBillTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE        = 'showcase';
    case PALLETS         = 'pallets';
    case SERVICES        = 'services';
    case PHYSICAL_GOODS  = 'physical_goods';
    case TRANSACTIONS    = 'transactions';

    case DATA    = 'data';
    case HISTORY = 'history';

    public function blueprint(): array
    {
        return match ($this) {
            RecurringBillTabsEnum::SHOWCASE => [
                'title' => __('stored item'),
                'icon'  => 'fas fa-info-circle',
            ],
            RecurringBillTabsEnum::PALLETS => [
                'title' => __('pallets'),
                'icon'  => 'fal fa-pallet',
            ],
            RecurringBillTabsEnum::SERVICES => [
                'title' => __('services'),
                'icon'  => 'fal fa-concierge-bell',
            ],
            RecurringBillTabsEnum::PHYSICAL_GOODS => [
                'title' => __('physical goods'),
                'icon'  => 'fal fa-cube',
            ],
            RecurringBillTabsEnum::TRANSACTIONS => [
                'title' => __('transactions'),
                'icon'  => 'fal fa-ballot',
            ],
            RecurringBillTabsEnum::DATA => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            RecurringBillTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
        };
    }
}
