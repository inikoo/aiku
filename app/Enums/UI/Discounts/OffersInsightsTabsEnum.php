<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Mon, 12 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Enums\UI\Discounts;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum OffersInsightsTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case OFFERS    = 'offers';
    case CAMPAIGNS = 'campaigns';

    public function blueprint(): array
    {
        return match ($this) {
            OffersInsightsTabsEnum::OFFERS => [
                'title' => __('Offers'),
                'icon'  => 'fal fa-badge-percent',
            ],
            OffersInsightsTabsEnum::CAMPAIGNS => [
                'title' => __('Campaigns'),
                'icon'  => 'fal fa-comment-dollar',
            ],
        };
    }
}
