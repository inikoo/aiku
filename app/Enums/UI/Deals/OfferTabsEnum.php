<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:24:19 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Deals;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum OfferTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case VOUCHERS            = 'vouchers';
    case ORDERS              = 'orders';
    case CUSTOMERS           = 'customers';

    case SETTINGS            = 'setting';
    case CHANGELOG           = 'changelog';


    public function blueprint(): array
    {
        return match ($this) {
            OfferTabsEnum::VOUCHERS => [
                'title' => __('vouchers'),
                'icon'  => 'fal fa-money-bill-wave',
            ],
            OfferTabsEnum::ORDERS => [
                'title' => __('orders'),
                'icon'  => 'fal fa-shopping-cart',
            ],
            OfferTabsEnum::CUSTOMERS => [
                'title' => __('customers'),
                'icon'  => 'fal fa-users',
            ],
            OfferTabsEnum::SETTINGS => [
                'title' => __('settings'),
                'icon'  => 'fal fa-slider-h',
                'type'  => 'icon-only',
            ],OfferTabsEnum::CHANGELOG => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-road',
                'type'  => 'icon-only',
            ],
        };
    }
}
