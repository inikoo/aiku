<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 06 Aug 2024 10:14:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Inventory;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum OrgStockFamilyTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case SHOWCASE           = 'showcase';

    case STOCKS              = 'stocks';
    //    case SALES              = 'sales';
    //    case ISSUES             = 'issues';
    //
    //
    //

    //    case PRODUCT_FAMILIES   = 'product_families';
    //    case PRODUCTS           = 'products';



    case HISTORY            = 'history';
    case DATA               = 'data';
    case IMAGES             = 'images';





    public function blueprint(): array
    {
        return match ($this) {

            OrgStockFamilyTabsEnum::DATA => [
                'type'  => 'icon',
                'align' => 'right',
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            OrgStockFamilyTabsEnum::STOCKS => [
                'title' => __('stocks'),
                'icon'  => 'fal fa-box',
            ],
            //            StockFamilyTabsEnum::SALES => [
            //                'title' => __('sales'),
            //                'icon'  => 'fal fa-dollar-sign',
            //            ],
            //            StockFamilyTabsEnum::ISSUES => [
            //                'title' => __('issues'),
            //                'icon'  => 'fal fa-poop',
            //            ],


            //            ],StockFamilyTabsEnum::PRODUCT_FAMILIES => [
            //                'title' => __('product families'),
            //                'icon'  => 'fal fa-cubes',
            //            ],StockFamilyTabsEnum::PRODUCTS => [
            //                'title' => __('products'),
            //                'icon'  => 'fal fa-cube',
            //            ],
            OrgStockFamilyTabsEnum::HISTORY => [
                'align' => 'right',
                'title' => __('changelog'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
            ],OrgStockFamilyTabsEnum::IMAGES => [
                'align' => 'right',
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon',
            ],
            OrgStockFamilyTabsEnum::SHOWCASE => [
                'title' => __('stock family'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
