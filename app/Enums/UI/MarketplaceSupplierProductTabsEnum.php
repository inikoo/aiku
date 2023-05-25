<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum MarketplaceSupplierProductTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE           = 'showcase';
    case ISSUES             = 'issues';
    case HISTORY            = 'history';
    case DATA               = 'data';
    case ATTACHMENTS        = 'attachments';
    case IMAGES             = 'images';



    public function blueprint(): array
    {
        return match ($this) {

            MarketplaceSupplierProductTabsEnum::SHOWCASE => [
                'title' => __('supplier product'),
                'icon'  => 'fas fa-info-circle',
            ],

            MarketplaceSupplierProductTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],

            MarketplaceSupplierProductTabsEnum::ISSUES => [
                'title' => __('issues'),
                'icon'  => 'fal fa-poop',
                'type'  => 'icon',
                'align' => 'right',
            ],
            MarketplaceSupplierProductTabsEnum::IMAGES => [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon',
                'align' => 'right',
            ],
            MarketplaceSupplierProductTabsEnum::ATTACHMENTS => [
                'title' => __('attachments'),
                'icon'  => 'fal fa-paperclip',
                'type'  => 'icon',
                'align' => 'right',
            ],
            MarketplaceSupplierProductTabsEnum::HISTORY => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right'
            ],


        };
    }
}
