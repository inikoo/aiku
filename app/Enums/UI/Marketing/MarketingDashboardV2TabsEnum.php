<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Enums\UI\Marketing;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum MarketingDashboardV2TabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case ACQUISITION = 'acquisition';
    case ORDERS = 'orders';
    case JOURNEY = 'journey';

    public function blueprint(): array
    {
        return match ($this) {
            MarketingDashboardV2TabsEnum::ACQUISITION => [
                'title' => __('Acquisition'),
                'icon'  => 'fal fa-user-plus',
            ],
            MarketingDashboardV2TabsEnum::ORDERS => [
                'title' => __('Orders'),
                'icon'  => 'fal fa-shopping-cart',
            ],
            MarketingDashboardV2TabsEnum::JOURNEY => [
                'title' => __('Journey'),
                'icon'  => 'fal fa-route',
            ],
        };
    }
}
