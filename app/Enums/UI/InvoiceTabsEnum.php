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

    case SHOWCASE               = 'showcase';
    case ITEMS                  = 'items';
    case PAYMENTS               = 'payments';
    case PROPERTIES_OPERATION   = 'properties_operation';
    case HISTORY                = 'history';
    case DATA                   = 'data';


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
                'icon'  => 'fal fa-percent',
            ],
            InvoiceTabsEnum::DATA     => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            InvoiceTabsEnum::HISTORY     => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            InvoiceTabsEnum::SHOWCASE => [
                'title' => __('invoice'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
