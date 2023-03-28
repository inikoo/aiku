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

    case STATS            = 'stats';
    case PAYMENTS         = 'payments';
    case DATA             = 'data';
    case CHANGELOG        = 'changelog';

    public function blueprint(): array
    {
        return match ($this) {
            PaymentAccountTabsEnum::STATS             => [
                'title' => __('stats'),
                'icon'  => 'fal fa-chart-line',
            ],
            PaymentAccountTabsEnum::PAYMENTS     => [
                'title' => __('payments'),
                'icon'  => 'fal fa-credit-card',
            ],
            PaymentAccountTabsEnum::DATA     => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            PaymentAccountTabsEnum::CHANGELOG     => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-clock',
            ],
        };
    }
}
