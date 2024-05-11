<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:16:21 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\SupplyChain;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum StockTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case SHOWCASE           = 'showcase';
    case SALES              = 'sales';
    case STOCK_HISTORY      = 'stock_history';

    case ISSUES              = 'issues';
    case PURCHASE_ORDERS     = 'purchase_orders';
    case SUPPLIERS_PRODUCTS  = 'supplier_products';
    case PRODUCTS            = 'product';
    case LOCATIONS           = 'locations';
    case IMAGES              = 'images';

    case HISTORY             = 'history';

    case DATA                = 'data';

    case ATTACHMENTS         = 'attachments';




    public function blueprint(): array
    {
        return match ($this) {
            StockTabsEnum::DATA => [
                'type'  => 'icon',
                'align' => 'right',
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
                'align' => 'right',
                'title' => __('attachments'),
                'icon'  => 'fal fa-paperclip',
                'type'  => 'icon',
            ],
            StockTabsEnum::IMAGES => [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon-only',
            ],
            StockTabsEnum::HISTORY => [
                'align' => 'right',
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
            ],
            StockTabsEnum::SHOWCASE => [
                'title' => __('stock'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
