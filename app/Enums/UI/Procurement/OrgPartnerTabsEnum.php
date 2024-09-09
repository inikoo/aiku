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


    case SHOWCASE              = 'showcase';
    case ORG_SUPPLIERS         = 'org_suppliers';
    case ORG_SUPPLIER_PRODUCTS = 'org_supplier_products';
    case PURCHASE_ORDERS       = 'purchase_orders';
    case DELIVERIES            = 'deliveries';
    case SYSTEM_USERS          = 'system_users';
    case HISTORY               = 'history';
    case DATA                  = 'data';
    case IMAGES                = 'images';


    public function blueprint(): array
    {
        return match ($this) {
            OrgPartnerTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            OrgPartnerTabsEnum::ORG_SUPPLIERS => [
                'title' => __('suppliers'),
                'icon'  => 'fal fa-person-dolly',
            ],
            OrgPartnerTabsEnum::ORG_SUPPLIER_PRODUCTS => [
                'title' => __('products'),
                'icon'  => 'fal fa-box-usd',
            ],
            OrgPartnerTabsEnum::PURCHASE_ORDERS => [
                'title' => __('purchase orders'),
                'icon'  => 'fal fa-clipboard',
            ],
            OrgPartnerTabsEnum::DELIVERIES => [
                'title' => __('deliveries'),
                'icon'  => 'fal fa-truck',
            ],
            OrgPartnerTabsEnum::IMAGES => [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon',
                'align' => 'right',
            ],
            OrgPartnerTabsEnum::SYSTEM_USERS => [
                'title' => __('system user'),
                'icon'  => 'fal fa-terminal',
            ],
            OrgPartnerTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            OrgPartnerTabsEnum::SHOWCASE => [
                'title' => __('agent'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
