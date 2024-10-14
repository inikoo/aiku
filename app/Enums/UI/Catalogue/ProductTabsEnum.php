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


    case SALES      = 'sales';


    case VARIATIONS = 'variations';


    case HISTORY = 'history';

    case IMAGES   = 'images';
    case PARTS    = 'parts';
    // case CUSTOMERS  = 'customers';
    case ORDERS     = 'orders';
    // case MAILSHOTS  = 'mailshots';
    case FAVOURITES          = 'favourites';


    public function blueprint(): array
    {
        return match ($this) {
            ProductTabsEnum::SHOWCASE => [
                'title' => __('overview'),
                'icon'  => 'fal fa-tachometer-alt-fast',
            ],
            ProductTabsEnum::VARIATIONS => [
                'title' => __('variations'),
                'icon'  => 'fal fa-stream',
            ],
            ProductTabsEnum::SALES => [
                'title' => __('sales'),
                'icon'  => 'fal fa-money-bill-wave',
            ],
            ProductTabsEnum::ORDERS => [
                'title' => __('orders'),
                'icon'  => 'fal fa-shopping-cart',
                'type'  => 'icon',
                'align' => 'right',
            ],
            // ProductTabsEnum::CUSTOMERS => [
            //     'title' => __('customers'),
            //     'icon'  => 'fal fa-user',
            //     'type'  => 'icon',
            //     'align' => 'right',

            // ],
            // ProductTabsEnum::MAILSHOTS => [
            //     'title' => __('mailshots'),
            //     'icon'  => 'fal fa-bullhorn',
            //     'type'  => 'icon',
            //     'align' => 'right',

            // ],
            ProductTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
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
            ],
            ProductTabsEnum::FAVOURITES => [
                'title' => __('favourites'),
                'icon'  => 'fal fa-heart',
                'align' => 'right',
                'type'  => 'icon',
            ],
        };
    }
}
