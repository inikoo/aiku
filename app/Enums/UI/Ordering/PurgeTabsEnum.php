<?php
/*
 * author Arya Permana - Kirin
 * created on 04-11-2024-15h-21m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Enums\UI\Ordering;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum PurgeTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE                       = 'showcase';
    case ORDERS                         = 'orders';






    public function blueprint(): array
    {
        return match ($this) {
            PurgeTabsEnum::SHOWCASE => [
                'title' => __('showcase'),
                'icon'  => 'fal fa-info-circle',
            ],
            PurgeTabsEnum::ORDERS => [
                'type'  => 'icon',
                'align' => 'right',
                'title' => __('orders'),
                'icon'  => 'fal fa-dollar-sign',
            ],
        };
    }
}