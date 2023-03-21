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


    case DATA                   = 'data';
    case VARIATIONS             = 'variations';
    case WEBPAGES               = 'webpages';
    case SALES                  = 'sales';
    case ORDERS                 = 'orders';
    case CUSTOMERS              = 'customers';
    case MAILSHOTS              = 'mailshots';
    case RELATED_PRODUCTS       = 'related_products';
    case PARTS                  = 'parts';
    case IMAGES                 = 'images';

    case CHANGELOG              = 'changelog';


    public function blueprint(): array
    {
        return match ($this) {
            ProductTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],ProductTabsEnum::VARIATIONS => [
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

            ProductTabsEnum::RELATED_PRODUCTS => [
                'title' => __('related products'),
                'icon'  => 'fal fa-project-diagram',

            ],
            ProductTabsEnum::PARTS => [
                'title' => __('parts'),
                'icon'  => 'fal fa-box',
                'type'  => 'icon-only'
            ],
            ProductTabsEnum::IMAGES => [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon-only',
            ],ProductTabsEnum::CHANGELOG => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-road',
                'type'  => 'icon-only',
            ],
        };
    }
}
