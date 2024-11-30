<?php

/*
 * author Arya Permana - Kirin
 * created on 24-10-2024-09h-35m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Enums\UI\SupplyChain;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum OrgStocksTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case ORG_STOCKS            = 'org_stocks';
    case ORG_STOCK_FAMILIES    = 'org_stock_families';





    public function blueprint(): array
    {
        return match ($this) {
            OrgStocksTabsEnum::ORG_STOCKS  => [
                'title' => __('stocks'),
                'icon'  => 'fal fa-box',
            ],
            OrgStocksTabsEnum::ORG_STOCK_FAMILIES => [
                'title' => __('stock families'),
                'icon'  => 'fal fa-box-usd',
            ],
        };
    }
}
