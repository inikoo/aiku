<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 10 Apr 2024 20:08:38 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Accounting;

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
