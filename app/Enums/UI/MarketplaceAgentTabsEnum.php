<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum MarketplaceAgentTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE            = 'SHOWCASE';
    case  SUPPLIERS          = 'suppliers';
    case SUPPLIER_PRODUCTS   = 'supplier_products';

    case HISTORY             = 'history';

    case DATA                = 'data';





    public function blueprint(): array
    {
        return match ($this) {
            MarketplaceAgentTabsEnum::DATA     => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            MarketplaceAgentTabsEnum::HISTORY     => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            MarketplaceAgentTabsEnum::SUPPLIERS  => [
                'title' => __('suppliers'),
                'icon'  => 'fal fa-store-alt',
            ],
            MarketplaceAgentTabsEnum::SUPPLIER_PRODUCTS => [
                'title' => __('supplier products'),
                'icon'  => 'fal fa-cube',
            ],
            MarketplaceAgentTabsEnum::SHOWCASE => [
                'title' => __('Agent'),
                'icon'  => 'fas fa-info-circle',
            ],

        };
    }
}
