<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum AgentTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE            = 'showcase';
    case  SUPPLIERS          = 'suppliers';
    case SUPPLIER_PRODUCTS   = 'supplier_products';

    case HISTORY             = 'history';

    case DATA                = 'data';





    public function blueprint(): array
    {
        return match ($this) {
            AgentTabsEnum::DATA     => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            AgentTabsEnum::HISTORY     => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            AgentTabsEnum::SUPPLIERS  => [
                'title' => __('suppliers'),
                'icon'  => 'fal fa-person-dolly',
            ],
            AgentTabsEnum::SUPPLIER_PRODUCTS => [
                'title' => __('products'),
                'icon'  => 'fal fa-box-usd',
            ],
            AgentTabsEnum::SHOWCASE => [
                'title' => __('OrgAgent'),
                'icon'  => 'fas fa-info-circle',
            ],

        };
    }
}
