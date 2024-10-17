<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 09:29:02 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Ordering;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum OrdersTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case ORDERS    = 'orders';
    case STATS     = 'stats';
    case INVOICES       = 'invoices';
    case DELIVERY_NOTES = 'delivery_notes';
    case HISTORY   = 'history';

    public function blueprint(): array
    {
        return match ($this) {

            OrdersTabsEnum::ORDERS => [
                'title' => __('orders'),
                'icon'  => 'fal fa-bars',
            ],
            OrdersTabsEnum::STATS => [
                'title' => __('stats'),
                'icon'  => 'fal fa-chart-pie',
            ],
            OrdersTabsEnum::INVOICES => [
                'title' => __('invoices'),
                'icon'  => 'fal fa-file-invoice-dollar'

            ],
            OrdersTabsEnum::DELIVERY_NOTES => [
                'title' => __('delivery notes'),
                'icon'  => 'fal fa-truck'
            ],
            OrdersTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right'
            ]
        };
    }
}
