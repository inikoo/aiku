<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 Mar 2023 01:54:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum PaymentAccountTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE         = 'showcase';
    case STATS            = 'stats';
    case PAYMENTS         = 'payments';
    case HISTORY          = 'history';
    case DATA             = 'data';


    public function blueprint(): array
    {
        return match ($this) {
            PaymentAccountTabsEnum::STATS             => [
                'title' => __('stats'),
                'icon'  => 'fal fa-chart-line',
            ],
            PaymentAccountTabsEnum::PAYMENTS     => [
                'title' => __('payments'),
                'icon'  => 'fal fa-coins',
            ],
            PaymentAccountTabsEnum::DATA     => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            PaymentAccountTabsEnum::HISTORY     => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            PaymentAccountTabsEnum::SHOWCASE => [
                'title' => __('payment account'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
