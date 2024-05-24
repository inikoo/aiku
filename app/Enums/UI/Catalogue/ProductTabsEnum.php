<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 20:49:54 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Catalogue;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ProductTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE   = 'showcase';
    case VARIATIONS = 'variations';
    case WEBPAGES   = 'webpages';
    case SALES      = 'sales';
    case ORDERS     = 'orders';
    case CUSTOMERS  = 'customers';
    case MAILSHOTS  = 'mailshots';

    case HISTORY = 'history';

    case DATA   = 'data';
    case IMAGES = 'images';
    case PARTS  = 'parts';
    case SERVICE  = 'service';
    case RENTAL  = 'rental';



    public function blueprint(): array
    {
        return match ($this) {
            ProductTabsEnum::SHOWCASE => [
                'title' => __('product'),
                'icon'  => 'fas fa-info-circle',
            ],
            ProductTabsEnum::VARIATIONS => [
                'title' => __('variations'),
                'icon'  => 'fal fa-stream',
            ],
            ProductTabsEnum::WEBPAGES => [
                'title' => __('webpages'),
                'icon'  => 'fal fa-globe',
            ],
            ProductTabsEnum::SALES => [
                'title' => __('sales'),
                'icon'  => 'fal fa-money-bill-wave',
            ],
            ProductTabsEnum::ORDERS => [
                'title' => __('orders'),
                'icon'  => 'fal fa-shopping-cart',
            ],
            ProductTabsEnum::CUSTOMERS => [
                'title' => __('customers'),
                'icon'  => 'fal fa-users',

            ],
            ProductTabsEnum::MAILSHOTS => [
                'title' => __('mailshots'),
                'icon'  => 'fal fa-bullhorn',

            ],


            ProductTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            ProductTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            ProductTabsEnum::PARTS => [
                'title' => __('parts'),
                'icon'  => 'fal fa-box',
                'type'  => 'icon',
                'align' => 'right',
            ],
            ProductTabsEnum::SERVICE => [
                'title' => __('service'),
                'icon'  => 'fal fa-box',
                'type'  => 'icon',
                'align' => 'right',
            ],
            ProductTabsEnum::RENTAL => [
                'title' => __('rental'),
                'icon'  => 'fal fa-box',
                'type'  => 'icon',
                'align' => 'right',
            ],

            ProductTabsEnum::IMAGES => [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon',
                'align' => 'right',
            ]
        };
    }
}
