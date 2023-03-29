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
            AgentTabsEnum::DATA     => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            AgentTabsEnum::SUPPLIERS  => [
                'title' => __('suppliers'),
                'icon'  => 'fal fa-store-alt',
            ],
            AgentTabsEnum::SUPPLIER_PRODUCTS => [
                'title' => __('supplier products'),
                'icon'  => 'fal fa-cube',
            ],
            AgentTabsEnum::PURCHASE_ORDERS     => [
                'title' => __('purchase orders'),
                'icon'  => 'fal fa-shopping-cart',
            ],AgentTabsEnum::DELIVERIES     => [
                'title' => __('deliveries'),
                'icon'  => 'fal fa-truck',
            ],AgentTabsEnum::IMAGES     => [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon',
                'align' => 'right',
            ],AgentTabsEnum::SYSTEM_USERS     => [
                'title' => __('system user'),
                'icon'  => 'fal fa-terminal',
            ],AgentTabsEnum::HISTORY     => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ]
        };
    }
}
