<?php

namespace App\Enums\UI\Procurement;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum OrgSupplierProductsTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case DASHBOARD = 'dashboard';
    case SUPPLIERS = 'suppliers';
    case INDEX = 'index';
    case HISTORY = 'history';

    public const array AGENT_TABS = [self::DASHBOARD->value, self::SUPPLIERS->value, self::INDEX->value];

    public function blueprint(): array
    {
        return match ($this) {
            self::DASHBOARD => [
                'title' => __('Data to fix'),
                'icon'  => 'fal fa-tachometer-alt',
            ],
            self::SUPPLIERS => [
                'title' => __('By supplier'),
                'icon'  => 'fal fa-person-dolly',
            ],
            self::INDEX => [
                'title' => __('Index'),
                'icon'  => 'fal fa-tachometer-alt-fast',
            ],
            self::HISTORY => [
                'title' => __('History'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
        };
    }
}
