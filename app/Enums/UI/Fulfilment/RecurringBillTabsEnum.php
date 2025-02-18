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

    case TRANSACTIONS    = 'transactions';
    case PALLET_DELIVERIES    = 'pallet_deliveries';
    case PALLET_RETURNS    = 'pallet_returns';

    // case DATA    = 'data';
    case HISTORY = 'history';

    public function blueprint(): array
    {
        return match ($this) {
            RecurringBillTabsEnum::TRANSACTIONS => [
                'title' => __('transactions'),
                'icon'  => 'fal fa-ballot',
            ],
            RecurringBillTabsEnum::PALLET_DELIVERIES => [
                'title' => __('pallet deliveries'),
                'icon'  => 'fal fa-truck',
            ],
            RecurringBillTabsEnum::PALLET_RETURNS => [
                'title' => __('pallet returns'),
                'icon'  => 'fal fa-ballot',
            ],
            // RecurringBillTabsEnum::DATA => [
            //     'align' => 'right',
            //     'type'  => 'icon',
            //     'title' => __('data'),
            //     'icon'  => 'fal fa-database',
            // ],
            RecurringBillTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
        };
    }
}
