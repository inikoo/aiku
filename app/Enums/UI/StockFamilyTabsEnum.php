<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum StockFamilyTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case DATA               = 'data';
    case SALES              = 'sales';
    case ISSUES             = 'issues';

    case PARTS              = 'parts';
    case DISCONTINUED_PARTS = 'discontinued_parts';
    case LOCATIONS          = 'locations';
    case PRODUCT_FAMILIES   = 'product_families';
    case PRODUCTS           = 'products';

    case CHANGELOG          = 'changelog';
    case IMAGES             = 'images';


    public function blueprint(): array
    {
        return match ($this) {
            StockFamilyTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            StockFamilyTabsEnum::SALES => [
                'title' => __('sales'),
                'icon'  => 'fal fa-dollar-sign',
            ],
            StockFamilyTabsEnum::ISSUES => [
                'title' => __('issues'),
                'icon'  => 'fal fa-poop',
            ],
            StockFamilyTabsEnum::PARTS => [
                'title' => __('parts'),
            ],StockFamilyTabsEnum::DISCONTINUED_PARTS => [
                'title' => __('discontinued parts'),
            ],StockFamilyTabsEnum::LOCATIONS => [
                'title' => __('locations'),
            ],StockFamilyTabsEnum::PRODUCT_FAMILIES => [
                'title' => __('product families'),
                'icon'  => 'fal fa-cubes',
            ],StockFamilyTabsEnum::PRODUCTS => [
                'title' => __('products'),
                'icon'  => 'fal fa-cube',
            ],StockFamilyTabsEnum::CHANGELOG => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-road',
                'type'  => 'icon-only',
            ],StockFamilyTabsEnum::IMAGES => [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon-only',
            ],
        };
    }
}
