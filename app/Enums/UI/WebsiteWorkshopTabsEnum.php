<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum WebsiteWorkshopTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case COLOR_SCHEME         = 'color_scheme';
    case MENU                 = 'menu';
    case CATEGORY             = 'category';
    case PRODUCT              = 'product';
    case LAYOUT               = 'layout';




    public function blueprint(): array
    {
        return match ($this) {
            WebsiteWorkshopTabsEnum::COLOR_SCHEME => [
                'title' => __('color scheme'),
                'icon'  => 'fal fa-palette',
            ],
            WebsiteWorkshopTabsEnum::MENU => [
                'title' => __('menu'),
                'icon'  => 'fal fa-bars',
            ],
            WebsiteWorkshopTabsEnum::CATEGORY => [
                'title' => __('category'),
                'icon'  => 'fal fa-cookie-bite',
            ],
            WebsiteWorkshopTabsEnum::PRODUCT => [
                'title' => __('product'),
                'icon'  => 'fal fa-cube',
            ],
            WebsiteWorkshopTabsEnum::LAYOUT => [
                'title' => __('layout'),
                'icon'  => 'fal fa-cube',
            ],
        };
    }
}
