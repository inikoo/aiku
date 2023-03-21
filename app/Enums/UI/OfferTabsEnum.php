<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

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
