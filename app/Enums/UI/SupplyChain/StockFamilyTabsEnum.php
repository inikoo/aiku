<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:17:18 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\SupplyChain;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum StockFamilyTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case SHOWCASE           = 'showcase';

    case STOCK              = 'stocks';
    //    case SALES              = 'sales';
    //    case ISSUES             = 'issues';
    //
    //
    //
    //    case PARTS              = 'parts';
    //    case DISCONTINUED_PARTS = 'discontinued_parts';
    //    case LOCATIONS          = 'locations';
    //    case PRODUCT_FAMILIES   = 'product_families';
    //    case PRODUCTS           = 'products';



    case HISTORY            = 'history';
    case DATA               = 'data';
    case IMAGES             = 'images';





    public function blueprint(): array
    {
        return match ($this) {

            StockFamilyTabsEnum::DATA => [
                'type'  => 'icon',
                'align' => 'right',
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            StockFamilyTabsEnum::STOCK => [
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
            //            StockFamilyTabsEnum::PARTS => [
            //                'title' => __('parts'),
            //                'icon'  => 'fal fa-box',
            //            ],StockFamilyTabsEnum::DISCONTINUED_PARTS => [
            //                'title' => __('discontinued parts'),
            //                'icon'  => 'fa-thing fa-x ',
            //            ],StockFamilyTabsEnum::LOCATIONS => [
            //                'title' => __('locations'),
            //                'icon'  => 'fal fa-inventory'
            //            ],StockFamilyTabsEnum::PRODUCT_FAMILIES => [
            //                'title' => __('product families'),
            //                'icon'  => 'fal fa-cubes',
            //            ],StockFamilyTabsEnum::PRODUCTS => [
            //                'title' => __('products'),
            //                'icon'  => 'fal fa-cube',
            //            ],
            StockFamilyTabsEnum::HISTORY => [
                'align' => 'right',
                'title' => __('changelog'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
            ],StockFamilyTabsEnum::IMAGES => [
                'align' => 'right',
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon',
            ],
            StockFamilyTabsEnum::SHOWCASE => [
                'title' => __('stock family'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
