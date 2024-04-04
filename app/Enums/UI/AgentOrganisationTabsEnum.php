<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum AgentOrganisationTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE            = 'showcase';
    case  SUPPLIERS          = 'suppliers';
    case SUPPLIER_PRODUCTS   = 'supplier_products';
    case PURCHASE_ORDERS     = 'purchase_orders';
    case DELIVERIES          = 'deliveries';
    case SYSTEM_USERS        = 'system_users';

    case HISTORY             = 'history';

    case DATA                = 'data';
    case IMAGES              = 'images';





    public function blueprint(): array
    {
        return match ($this) {
            AgentOrganisationTabsEnum::DATA     => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            AgentOrganisationTabsEnum::SUPPLIERS  => [
                'title' => __('suppliers'),
                'icon'  => 'fal fa-person-dolly',
            ],
            AgentOrganisationTabsEnum::SUPPLIER_PRODUCTS => [
                'title' => __('products'),
                'icon'  => 'fal fa-box-usd',
            ],
            AgentOrganisationTabsEnum::PURCHASE_ORDERS     => [
                'title' => __('purchase orders'),
                'icon'  => 'fal fa-clipboard',
            ],AgentOrganisationTabsEnum::DELIVERIES     => [
                'title' => __('deliveries'),
                'icon'  => 'fal fa-truck',
            ],AgentOrganisationTabsEnum::IMAGES     => [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon',
                'align' => 'right',
            ],AgentOrganisationTabsEnum::SYSTEM_USERS     => [
                'title' => __('system user'),
                'icon'  => 'fal fa-terminal',
            ],AgentOrganisationTabsEnum::HISTORY     => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            AgentOrganisationTabsEnum::SHOWCASE => [
                'title' => __('agent'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
