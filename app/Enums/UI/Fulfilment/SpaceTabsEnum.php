<?php

/*
 * author Arya Permana - Kirin
 * created on 30-01-2025-16h-38m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Enums\UI\Fulfilment;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum SpaceTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case OVERVIEW                       = 'overview';

    public function blueprint(): array
    {
        return match ($this) {
            SpaceTabsEnum::OVERVIEW => [
                'title' => __('dashboard'),
                'icon'  => 'fal fa-tachometer-alt-fast',
            ],
        };
    }
}
