<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum PartFamilyTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case DATA               = 'data';
    case SALES              = 'sales';
    case ISSUES             = 'issues';

    case PARTS              = 'parts';
    case DISCONTINUED_PARTS = 'discontinued_parts';
    case PARTS_LOCATIONS    = 'parts_locations';
    case PRODUCT_FAMILIES   = 'product_families';
    case PRODUCTS           = 'products';

    case CHANGELOG          = 'changelog';
    case IMAGES             = 'images';


    public function blueprint(): array
    {
        return match ($this) {
            PartFamilyTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            PartFamilyTabsEnum::SALES => [
                'title' => __('sales'),
                'icon'  => 'fal fa-dollar-sign',
            ],
            PartFamilyTabsEnum::ISSUES => [
                'title' => __('issues'),
                'icon'  => 'fal fa-poop',
            ],
            PartFamilyTabsEnum::PARTS => [
                'title' => __('parts'),
            ],PartFamilyTabsEnum::DISCONTINUED_PARTS => [
                'title' => __('discontinued parts'),
            ],PartFamilyTabsEnum::PARTS_LOCATIONS => [
                'title' => __('parts locations'),
            ],PartFamilyTabsEnum::PRODUCT_FAMILIES => [
                'title' => __('product families'),
                'icon'  => 'fal fa-cubes',
            ],PartFamilyTabsEnum::PRODUCTS => [
                'title' => __('products'),
                'icon'  => 'fal fa-cube',
            ],PartFamilyTabsEnum::CHANGELOG => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-road',
                'type'  => 'icon-only',
            ],PartFamilyTabsEnum::IMAGES => [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon-only',
            ],
        };
    }
}
