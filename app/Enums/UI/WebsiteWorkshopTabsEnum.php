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


    case HEADER             = 'header';
    case MENU               = 'menu';
    case FOOTER             = 'footer';



    public function blueprint(): array
    {
        return match ($this) {
            WebsiteWorkshopTabsEnum::HEADER => [
                'title' => __('header'),
                'icon'  => 'fal fa-arrow-alt-to-top',
            ],
            WebsiteWorkshopTabsEnum::MENU => [
                'title' => __('menu'),
                'icon'  => 'fal fa-bars',
            ],
            WebsiteWorkshopTabsEnum::FOOTER => [
                'title' => __('footer'),
                'icon'  => 'fal fa-arrow-alt-to-bottom',
            ],
        };
    }
}
