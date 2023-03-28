<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum StockTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case DATA               = 'data';
    case SALES              = 'sales';
    case STOCK_HISTORY      = 'stock_history';

    case ISSUES              = 'issues';
    case PURCHASE_ORDERS     = 'purchase_orders';
    case SUPPLIERS_PRODUCTS  = 'supplier_products';
    case PRODUCTS            = 'product';
    case LOCATIONS           = 'locations';
    case ATTACHMENTS         = 'attachments';
    case IMAGES              = 'images';

    case CHANGELOG          = 'changelog';


    public function blueprint(): array
    {
        return match ($this) {
            StockTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            StockTabsEnum::SALES => [
                'title' => __('sales'),
                'icon'  => 'fal fa-dollar-sign',
            ],
            StockTabsEnum::STOCK_HISTORY => [
                'title' => __('stock history'),
                'icon'  => 'fal fa-scanner',
            ],
            StockTabsEnum::ISSUES => [
                'title' => __('issue'),
                'icon'  => 'fal fa-poop',
            ],
            StockTabsEnum::PURCHASE_ORDERS => [
                'title' => __('purchase orders'),
                'icon'  => 'fal fa-clipboard',
            ],
            StockTabsEnum::SUPPLIERS_PRODUCTS => [
                'title' => __('supplier product'),
                'icon'  => 'fal fa-hand-receiving',
            ],
            StockTabsEnum::PRODUCTS => [
                'title' => __('products'),
                'icon'  => 'fal fa-cube',
            ],
            StockTabsEnum::LOCATIONS => [
                'title' => __('locations'),
                'icon'  => 'fal fa-inventory',
                'type'  => 'icon-only',
            ],
            StockTabsEnum::ATTACHMENTS => [
                'title' => __('attachments'),
                'icon'  => 'fal fa-paperclip',
                'type'  => 'icon-only',
            ],
            StockTabsEnum::IMAGES => [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon-only',
            ],
            StockTabsEnum::CHANGELOG => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-road',
                'type'  => 'icon-only',
            ],
        };
    }
}
