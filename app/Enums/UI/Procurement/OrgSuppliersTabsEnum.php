<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Procurement;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum OrgSuppliersTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case DASHBOARD = 'dashboard';
    case INDEX = 'index';

    public function blueprint(): array
    {
        return match ($this) {
            self::DASHBOARD => [
                'title' => __('Dashboard'),
                'icon'  => 'fal fa-tachometer-alt',
            ],
            self::INDEX => [
                'title' => __('Suppliers'),
                'icon'  => 'fal fa-bars',
            ],
        };
    }
}
