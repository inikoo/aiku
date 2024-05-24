<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 20:49:54 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Catalogue;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ServiceTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE   = 'showcase';
    // case VARIATIONS = 'variations';
    // case WEBPAGES   = 'webpages';
    case SALES      = 'sales';
    case ORDERS     = 'orders';
    case CUSTOMERS  = 'customers';
    case MAILSHOTS  = 'mailshots';

    case HISTORY = 'history';

    case DATA   = 'data';
    case IMAGES = 'images';
    case PARTS  = 'parts';



    public function blueprint(): array
    {
        return match ($this) {
            ServiceTabsEnum::SHOWCASE => [
                'title' => __('product'),
                'icon'  => 'fas fa-info-circle',
            ],
            // ServiceTabsEnum::VARIATIONS => [
            //     'title' => __('variations'),
            //     'icon'  => 'fal fa-stream',
            // ],
            // ServiceTabsEnum::WEBPAGES => [
            //     'title' => __('webpages'),
            //     'icon'  => 'fal fa-globe',
            // ],
            ServiceTabsEnum::SALES => [
                'title' => __('sales'),
                'icon'  => 'fal fa-money-bill-wave',
            ],
            ServiceTabsEnum::ORDERS => [
                'title' => __('orders'),
                'icon'  => 'fal fa-shopping-cart',
            ],
            ServiceTabsEnum::CUSTOMERS => [
                'title' => __('customers'),
                'icon'  => 'fal fa-users',

            ],
            ServiceTabsEnum::MAILSHOTS => [
                'title' => __('mailshots'),
                'icon'  => 'fal fa-bullhorn',

            ],


            ServiceTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            ServiceTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            ServiceTabsEnum::PARTS => [
                'title' => __('parts'),
                'icon'  => 'fal fa-box',
                'type'  => 'icon',
                'align' => 'right',
            ],
            // ServiceTabsEnum::SERVICE => [
            //     'title' => __('service'),
            //     'icon'  => 'fal fa-box',
            //     'type'  => 'icon',
            //     'align' => 'right',
            // ],
            // ServiceTabsEnum::RENTAL => [
            //     'title' => __('rental'),
            //     'icon'  => 'fal fa-box',
            //     'type'  => 'icon',
            //     'align' => 'right',
            // ],

            ServiceTabsEnum::IMAGES => [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon',
                'align' => 'right',
            ]
        };
    }
}
