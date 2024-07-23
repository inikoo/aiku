<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:13:15 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Accounting;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum InvoiceTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE               = 'showcase';
    // case ITEMS                  = 'items';
    case HISTORY                = 'history';
    case PAYMENTS               = 'payments';


    public function blueprint(): array
    {
        return match ($this) {

            InvoiceTabsEnum::PAYMENTS     => [
                'title' => __('payments'),
                'type'  => 'icon',
                'align' => 'right',
                'icon'  => 'fal fa-credit-card',
            ],

            InvoiceTabsEnum::HISTORY     => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            InvoiceTabsEnum::SHOWCASE => [
                'title' => __('items'),
                'icon'  => 'fas fa-file-invoice',
            ],
            // InvoiceTabsEnum::ITEMS => [
            //     'title' => __('items'),
            //     'icon'  => 'fas fa-file-invoice',
            // ],
        };
    }
}
