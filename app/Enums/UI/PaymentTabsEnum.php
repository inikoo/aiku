<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 Mar 2023 01:54:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum PaymentTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case DATA                         = 'data';
    case COMMUNICATIONS_HISTORY_NOTES = 'communications_history_notes';
    case CHANGELOG                    = 'changelog';

    public function blueprint(): array
    {
        return match ($this) {
            PaymentTabsEnum::DATA             => [
                'title' => __('data'),
                'icon'  => 'fal fa-chart-line',
            ],

            PaymentTabsEnum::COMMUNICATIONS_HISTORY_NOTES     => [
                'title' => __('history, notes'),
                'icon'  => 'fal fa-database',
            ],

            PaymentTabsEnum::CHANGELOG     => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-clock',
            ],
        };
    }
}
