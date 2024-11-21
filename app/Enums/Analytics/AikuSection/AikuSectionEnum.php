<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 21-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Enums\Analytics\AikuSection;

enum AikuSectionEnum: string
{
    case GROUP_DASHBOARD = 'group-dashboard';
    case GROUP_GOODS = 'group-goods';
    case GROUP_SUPPLY_CHAIN = 'group-supply-chain';
    case GROUP_ORGANISATION = 'group-organisation';
    case GROUP_OVERVIEW = 'group-overview';
    case GROUP_SYSADMIN = 'group-sysadmin';
    case GROUP_PROFILE = 'group-profile';

    case ORG_DASHBOARD = 'org-dashboard';
    case ORG_SETTINGS = 'org-settings';
    case ORG_PROCUREMENT = 'org-procurement';
    case ORG_ACCOUNTING = 'org-accounting';
    case ORG_HR = 'org-hr';
    case ORG_REPORT = 'org-report';

    case SHOP_DASHBOARD = 'shop-dashboard';
    case SHOP_CATALOGUE = 'shop-catalogue';
    case SHOP_BILLABLES = 'shop-billables';
    case SHOP_OFFER = 'shop-offer';
    case SHOP_MARKETING = 'shop-marketing';
    case SHOP_WEBSITE = 'shop-website';
    case SHOP_CRM = 'shop-crm';
    case SHOP_ORDERING = 'shop-ordering';
    case SHOP_SETTINGS = 'shop-settings';

    case FULFILMENT_DASHBOARD = 'fulfilment-dashboard';
    case FULFILMENT_BILLABLES = 'fulfilment-billables';
    case FULFILMENT_OPERATION = 'fulfilment-operation';
    case FULFILMENT_WEBSITE = 'fulfilment-website';
    case FULFILMENT_CRM = 'fulfilment-crm';
    case FULFILMENT_SETTINGS = 'fulfilment-settings';

    case AGENT_DASHBOARD = 'agent-dashboard';
    case AGENT_PROCUREMENT = 'agent-procurement';

    case PRODUCTION_CRAFT = 'production-craft';
    case PRODUCTION_OPERATION = 'production-operation';


    case INVENTORY = 'inventory';
    case INVENTORY_INFRASTRUCTURE = 'inventory-infrastructure';
    case INVENTORY_INCOMING = 'inventory-incoming';
    case INVENTORY_DISPATCHING = 'inventory-dispatching';

    public static function labels(): array
    {
        return [
            'group-dashboard'    => __('Group Dashboard'),
            'group-goods'        => __('Group Goods'),
            'group-supply-chain' => __('Group Supply Chain'),
            'group-organisation' => __('Group Organisation'),
            'group-overview'     => __('Group Overview'),
            'group-sysadmin'     => __('Group SysAdmin'),
            'group-profile'      => __('Group Profile'),

            'org-dashboard'   => __('Organisation Dashboard'),
            'org-settings'    => __('Organisation Settings'),
            'org-procurement' => __('Organisation Procurement'),
            'org-accounting'  => __('Organisation Accounting'),
            'org-hr'          => __('Organisation HR'),
            'org-report'      => __('Organisation Report'),

            'shop-dashboard' => __('Shop Dashboard'),
            'shop-catalogue' => __('Shop Catalogue'),
            'shop-billables' => __('Shop Billables'),
            'shop-offer'     => __('Shop Offer'),
            'shop-marketing' => __('Shop Marketing'),
            'shop-website'   => __('Shop Website'),
            'shop-crm'       => __('Shop CRM'),
            'shop-ordering'  => __('Shop Ordering'),
            'shop-settings'  => __('Shop Settings'),

            'fulfilment-dashboard' => __('Fulfilment Dashboard'),
            'fulfilment-billables' => __('Fulfilment Billables'),
            'fulfilment-operation' => __('Fulfilment Operation'),
            'fulfilment-website'   => __('Fulfilment Website'),
            'fulfilment-crm'       => __('Fulfilment CRM'),
            'fulfilment-settings'  => __('Fulfilment Settings'),

            'agent-dashboard' => __('Agent Dashboard'),
            'agent-procurement' => __('Agent Procurement'),

            'production-craft'     => __('Production Craft'),
            'production-operation' => __('Production Operation'),

            'inventory'                => __('Inventory'),
            'inventory-infrastructure' => __('Inventory Infrastructure'),
            'inventory-incoming'       => __('Inventory Incoming'),
            'inventory-dispatching'    => __('Inventory Dispatching'),
        ];
    }

    public function scopeType(): array
    {
        return match ($this) {
            // Group section
            AikuSectionEnum::GROUP_DASHBOARD,
            AikuSectionEnum::GROUP_GOODS,
            AikuSectionEnum::GROUP_SUPPLY_CHAIN,
            AikuSectionEnum::GROUP_ORGANISATION,
            AikuSectionEnum::GROUP_OVERVIEW,
            AikuSectionEnum::GROUP_SYSADMIN,
            AikuSectionEnum::GROUP_PROFILE,
            => ['Group'],

            // Organisation section
            AikuSectionEnum::ORG_DASHBOARD,
            AikuSectionEnum::ORG_PROCUREMENT,
            => ['Organisation'],

            // Organisation + Agent + DigitalAgency share sections
            AikuSectionEnum::ORG_ACCOUNTING,
            AikuSectionEnum::ORG_HR,
            AikuSectionEnum::ORG_REPORT,
            AikuSectionEnum::ORG_SETTINGS,
            => ['Organisation', 'Agent', 'DigitalAgency'],

            // Shop section
            AikuSectionEnum::SHOP_DASHBOARD,
            AikuSectionEnum::SHOP_CATALOGUE,
            AikuSectionEnum::SHOP_BILLABLES,
            AikuSectionEnum::SHOP_OFFER,
            AikuSectionEnum::SHOP_MARKETING,
            AikuSectionEnum::SHOP_WEBSITE,
            AikuSectionEnum::SHOP_CRM,
            AikuSectionEnum::SHOP_ORDERING,
            AikuSectionEnum::SHOP_SETTINGS,
            => ['Shop'],

            // Fulfilment section
            AikuSectionEnum::FULFILMENT_DASHBOARD,
            AikuSectionEnum::FULFILMENT_BILLABLES,
            AikuSectionEnum::FULFILMENT_OPERATION,
            AikuSectionEnum::FULFILMENT_WEBSITE,
            AikuSectionEnum::FULFILMENT_CRM,
            AikuSectionEnum::FULFILMENT_SETTINGS,
            => ['Fulfilment'],

            AikuSectionEnum::AGENT_DASHBOARD,
            AikuSectionEnum::AGENT_PROCUREMENT,
            => ['Agent'],

            // Production section
            AikuSectionEnum::PRODUCTION_CRAFT,
            AikuSectionEnum::PRODUCTION_OPERATION,
            => ['Production'],

            // Inventory section
            AikuSectionEnum::INVENTORY,
            AikuSectionEnum::INVENTORY_INFRASTRUCTURE,
            AikuSectionEnum::INVENTORY_INCOMING,
            AikuSectionEnum::INVENTORY_DISPATCHING,
            => ['Warehouse'],
        };
    }

}
