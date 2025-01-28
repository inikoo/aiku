<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 23-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Enums\UI\Accounting;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum InvoiceRefundTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case ITEMS                  = 'items';
    case ITEMS_IN_PROCESS                  = 'items_in_process';
    case HISTORY                = 'history';
    case PAYMENTS               = 'payments';


    public function blueprint(): array
    {
        return match ($this) {

            InvoiceRefundTabsEnum::PAYMENTS     => [
                'title' => __('Payments'),
                'type'  => 'icon',
                'align' => 'right',
                'icon'  => 'fal fa-credit-card',
            ],

            InvoiceRefundTabsEnum::HISTORY     => [
                'title' => __('History'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],

            InvoiceRefundTabsEnum::ITEMS       => [
                'title' => __('Items'),
                'icon'  => 'fal fa-bars',
            ],

            InvoiceRefundTabsEnum::ITEMS_IN_PROCESS       => [
                'title' => __('Refund Items'),
                'icon'  => 'fal fa-undo-alt',
            ],
        };
    }
}
