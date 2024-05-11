<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 20:45:35 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Catalogue;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum FamilyTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;



    case SHOWCASE            = 'showcase';
    case PRODUCTS            = 'products';
    case SALES               = 'sales';
    case CUSTOMERS           = 'customers';
    case OFFERS              = 'offers';
    case MAILSHOTS           = 'mailshots';

    case HISTORY             = 'history';

    case DATA                = 'data';

    case IMAGES              = 'images';




    public function blueprint(): array
    {
        return match ($this) {
            FamilyTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            FamilyTabsEnum::PRODUCTS => [
                'title' => __('products'),
                'icon'  => 'fal fa-cube'
            ],
            FamilyTabsEnum::SALES => [
                'title' => __('sales'),
                'icon'  => 'fal fa-money-bill-wave',
            ],
            FamilyTabsEnum::CUSTOMERS => [
                'title' => __('customers'),
                'icon'  => 'fal fa-user',
            ],FamilyTabsEnum::OFFERS => [
                'title' => __('offers'),
                'icon'  => 'fal fa-tags',
            ],FamilyTabsEnum::MAILSHOTS => [
                'title' => __('mailshots'),
                'icon'  => 'fal fa-bullhorn',
            ],FamilyTabsEnum::IMAGES=> [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon',
                'align' => 'right',
            ],FamilyTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            FamilyTabsEnum::SHOWCASE => [
                'title' => __('family'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
