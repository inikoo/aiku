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

    case DATA                = 'data';
    case AGENTS_SUPPLIERS    = 'agents_suppliers';
    case AGENTS_PARTS        = 'agents_parts';
    case PURCHASE_ORDERS     = 'purchase_orders';
    case DELIVERIES          = 'deliveries';
    case IMAGES              = 'images';
    case SYSTEM_USERS        = 'system_users';

    case CHANGELOG           = 'changelog';




    public function blueprint(): array
    {
        return match ($this) {
            AgentTabsEnum::DATA     => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            AgentTabsEnum::AGENTS_SUPPLIERS             => [
                'title' => __('portfolio'),
                'icon'  => 'fal fa-store-alt',
            ],
            AgentTabsEnum::AGENTS_PARTS => [
                'title' => __('warehouse areas'),
                'icon'  => 'fal fa-cube',
            ],
            AgentTabsEnum::PURCHASE_ORDERS     => [
                'title' => __('locations'),
                'icon'  => 'fal fa-shopping-cart',
            ],AgentTabsEnum::DELIVERIES     => [
                'title' => __('sales'),
                'icon'  => 'fal fa-money-bill',
            ],AgentTabsEnum::IMAGES     => [
                'title' => __('insights'),
                'icon'  => 'fal fa-graduation-cap',
            ],AgentTabsEnum::SYSTEM_USERS     => [
                'title' => __('discounts'),
                'icon'  => 'fal fa-tags',
            ],AgentTabsEnum::CHANGELOG     => [
                'title' => __('credit blockchains'),
                'icon'  => 'fal fa-code-commit',
            ]
        };
    }
}
