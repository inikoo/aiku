<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 21-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Enums\Analytics;

enum SectionEnum: string
{
    case GROUP_DASHBOARD = 'group-dashboard';
    case GROUP_GOODS = 'group-goods';
    case GROUP_SUPPLY_CHAIN = 'group-supply-chain';
    case GROUP_ORGANISATION = 'group-organisation';
    case GROUP_OVERVIEW = 'group-overview';
    case GROUP_SYSADMIN = 'group-sysadmin';
    case GROUP_PROFILE = 'group-profile';

    case ORG_DASHBOARD = 'org-dashboard';
    case ORG_SETTING = 'org-setting';
    case ORG_PROCUREMENT    = 'org-procurement';
    case ORG_ACCOUNTING     = 'org-accounting';
    case ORG_HR             = 'org-hr';
    case ORG_REPORT         = 'org-report';

    case SHOP_DASHBOARD        = 'shop-dashboard';
    case SHOP_CATALOGUE             = 'shop-catalogue';
    case SHOP_BILLABLES             = 'shop-billables';
    case SHOP_OFFER                = 'shop-offer';
    case SHOP_MARKETING             = 'shop-marketing';
    case SHOP_WEBSITE               = 'shop-website';
    case SHOP_CRM                   = 'shop-crm';
    case SHOP_ORDERING              = 'shop-ordering';
    case SHOP_SETTING          = 'shop-setting';

    case FULFILMENT_DASHBOARD = 'fulfilment-dashboard';
    case FULFILMENT_BILLABLES = 'fulfilment-billables';
    case FULFILMENT_OPERATION = 'fulfilment-operation';
    case FULFILMENT_WEBSITE = 'fulfilment-website';
    case FULFILMENT_CRM     = 'fulfilment-crm';
    case FULFILMENT_SETTING = 'fulfilment-setting';

    case PRODUCTION_CRAFT           = 'production-craft';
    case PRODUCTION_OPERATION       = 'production-operation';


    case INVENTORY                  = 'inventory';
    case INVENTORY_INFRASTRUCTURE   = 'inventory-infrastructure';
    case INVENTORY_INCOMING         = 'inventory-incoming';
    case INVENTORY_DISPATCHING      = 'inventory-dispatching';

    public static function labels(): array
    {
        return [
            'group-dashboard'        => __('Group Dashboard'),
            'group-goods'            => __('Group Goods'),
            'group-supply-chain'     => __('Group Supply Chain'),
            'group-organisation'     => __('Group Organisation'),
            'group-overview'         => __('Group Overview'),
            'group-sysadmin'         => __('Group SysAdmin'),
            'group-profile'          => __('Group Profile'),

            'org-dashboard'          => __('Organisation Dashboard'),
            'org-setting'            => __('Organisation Setting'),
            'org-procurement'        => __('Organisation Procurement'),
            'org-accounting'         => __('Organisation Accounting'),
            'org-hr'                 => __('Organisation HR'),
            'org-report'             => __('Organisation Report'),

            'shop-dashboard'         => __('Shop Dashboard'),
            'shop-catalogue'         => __('Shop Catalogue'),
            'shop-billables'         => __('Shop Billables'),
            'shop-offer'             => __('Shop Offer'),
            'shop-marketing'         => __('Shop Marketing'),
            'shop-website'           => __('Shop Website'),
            'shop-crm'               => __('Shop CRM'),
            'shop-ordering'          => __('Shop Ordering'),
            'shop-setting'           => __('Shop Setting'),

            'fulfilment-dashboard'   => __('Fulfilment Dashboard'),
            'fulfilment-billables'   => __('Fulfilment Billables'),
            'fulfilment-operation'   => __('Fulfilment Operation'),
            'fulfilment-website'     => __('Fulfilment Website'),
            'fulfilment-crm'         => __('Fulfilment CRM'),
            'fulfilment-setting'     => __('Fulfilment Setting'),

            'production-craft'       => __('Production Craft'),
            'production-operation'   => __('Production Operation'),

            'inventory'                => __('Inventory'),
            'inventory-infrastructure' => __('Inventory Infrastructure'),
            'inventory-incoming'       => __('Inventory Incoming'),
            'inventory-dispatching'    => __('Inventory Dispatching'),
        ];
    }

    public function scopeType(): string
    {
        return match ($this) {
            // Group section
            SectionEnum::GROUP_DASHBOARD,
            SectionEnum::GROUP_GOODS,
            SectionEnum::GROUP_SUPPLY_CHAIN,
            SectionEnum::GROUP_ORGANISATION,
            SectionEnum::GROUP_OVERVIEW,
            SectionEnum::GROUP_SYSADMIN,
            SectionEnum::GROUP_PROFILE,
            => 'Group',

            // Organisation section
            SectionEnum::ORG_DASHBOARD,
            SectionEnum::ORG_SETTING,
            SectionEnum::ORG_PROCUREMENT,
            SectionEnum::ORG_ACCOUNTING,
            SectionEnum::ORG_HR,
            SectionEnum::ORG_REPORT,
            => 'Organisation',

            // Shop section
            SectionEnum::SHOP_DASHBOARD,
            SectionEnum::SHOP_CATALOGUE,
            SectionEnum::SHOP_BILLABLES,
            SectionEnum::SHOP_OFFER,
            SectionEnum::SHOP_MARKETING,
            SectionEnum::SHOP_WEBSITE,
            SectionEnum::SHOP_CRM,
            SectionEnum::SHOP_ORDERING,
            SectionEnum::SHOP_SETTING,
            => 'Shop',

            // Fulfilment section
            SectionEnum::FULFILMENT_DASHBOARD,
            SectionEnum::FULFILMENT_BILLABLES,
            SectionEnum::FULFILMENT_OPERATION,
            SectionEnum::FULFILMENT_WEBSITE,
            SectionEnum::FULFILMENT_CRM,
            SectionEnum::FULFILMENT_SETTING,
            => 'Fulfilment',

            // Production section
            SectionEnum::PRODUCTION_CRAFT,
            SectionEnum::PRODUCTION_OPERATION,
            => 'Production',

            // Inventory section
            SectionEnum::INVENTORY,
            SectionEnum::INVENTORY_INFRASTRUCTURE,
            SectionEnum::INVENTORY_INCOMING,
            SectionEnum::INVENTORY_DISPATCHING,
            => 'Warehouse',

        };
    }

}
