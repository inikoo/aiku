<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Aug 2024 17:14:04 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Procurement;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum OrgStockTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case SHOWCASE           = 'showcase';
    case SALES              = 'sales';
    case STOCK_HISTORY      = 'stock_history';




    case PURCHASE_ORDERS     = 'purchase_orders';
    case SUPPLIERS_PRODUCTS  = 'supplier_products';
    case PRODUCTS            = 'product';

    case HISTORY             = 'history';
    case IMAGES              = 'images';
    case ATTACHMENTS         = 'attachments';
    case ISSUES              = 'issues';



    public function blueprint(): array
    {
        return match ($this) {
            OrgStockTabsEnum::HISTORY => [
                'align' => 'right',
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
            ],
            OrgStockTabsEnum::SALES => [
                'title' => __('sales'),
                'icon'  => 'fal fa-dollar-sign',
            ],
            OrgStockTabsEnum::STOCK_HISTORY => [
                'title' => __('stock history'),
                'icon'  => 'fal fa-scanner',
            ],
            OrgStockTabsEnum::ISSUES => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('issue'),
                'icon'  => 'fal fa-poop',
            ],
            OrgStockTabsEnum::PURCHASE_ORDERS => [
                'title' => __('purchase orders'),
                'icon'  => 'fal fa-clipboard',
            ],
            OrgStockTabsEnum::SUPPLIERS_PRODUCTS => [
                'title' => __('supplier product'),
                'icon'  => 'fal fa-hand-receiving',
            ],
            OrgStockTabsEnum::PRODUCTS => [
                'title' => __('products'),
                'icon'  => 'fal fa-cube',
            ],

            OrgStockTabsEnum::ATTACHMENTS => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('attachments'),
                'icon'  => 'fal fa-paperclip',

            ],
            OrgStockTabsEnum::IMAGES => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
            ],

            OrgStockTabsEnum::SHOWCASE => [
                'title' => __('overview'),
                'icon'  => 'fal fa-tachometer-alt-fast',
            ],
        };
    }
}
