<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

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

            ProductTabsEnum::IMAGES => [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon',
                'align' => 'right',
            ]
        };
    }
}
