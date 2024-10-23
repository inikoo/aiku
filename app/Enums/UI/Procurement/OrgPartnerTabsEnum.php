<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Aug 2024 17:14:04 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Procurement;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum OrgPartnerTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case SHOWCASE = 'showcase';
    case ORG_STOCKS = 'org_stocks';
    case PURCHASE_ORDERS = 'purchase_orders';
    case DELIVERIES = 'deliveries';


    public function blueprint(): array
    {
        return match ($this) {
            OrgPartnerTabsEnum::ORG_STOCKS => [
                'title' => __('stocks'),
                'icon'  => 'fal fa-box',
            ],
            OrgPartnerTabsEnum::PURCHASE_ORDERS => [
                'title' => __('purchase orders'),
                'icon'  => 'fal fa-clipboard',
            ],
            OrgPartnerTabsEnum::DELIVERIES => [
                'title' => __('deliveries'),
                'icon'  => 'fal fa-truck',
            ],
            OrgPartnerTabsEnum::SHOWCASE => [
                'title' => __('overview'),
                'icon'  => 'fal fa-tachometer-alt-fast',
            ],
        };
    }
}
