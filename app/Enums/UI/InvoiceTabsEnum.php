<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 Mar 2023 01:54:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum InvoiceTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case ITEMS                  = 'items';
    case PAYMENTS               = 'payments';
    case PROPERTIES_OPERATION   = 'properties_operation';
    case CHANGELOG              = 'changelog';

    public function blueprint(): array
    {
        return match ($this) {
            InvoiceTabsEnum::ITEMS             => [
                'title' => __('items'),
                'icon'  => 'fal fa-chart-line',
            ],
            InvoiceTabsEnum::PAYMENTS     => [
                'title' => __('payments'),
                'icon'  => 'fal fa-credit-card',
            ],
            InvoiceTabsEnum::PROPERTIES_OPERATION     => [
                'title' => __('properties/operation'),
                'icon'  => 'fal fa-database',
            ],
            InvoiceTabsEnum::CHANGELOG     => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-clock',
            ],
        };
    }
}
