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
                'title' => __('invoice'),
                'icon'  => 'fas fa-file-invoice',
            ],
            InvoiceTabsEnum::ITEMS => [
                'title' => __('items'),
                'icon'  => 'fas fa-file-invoice',
            ],
        };
    }
}
