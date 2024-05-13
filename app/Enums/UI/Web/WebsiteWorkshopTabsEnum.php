<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:14:32 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Web;

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
    case PAGE_LAYOUT          = 'page_layout';




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
            WebsiteWorkshopTabsEnum::PAGE_LAYOUT => [
                'title' => __('layout'),
                'icon'  => 'fal fa-cube',
            ],
        };
    }
}
