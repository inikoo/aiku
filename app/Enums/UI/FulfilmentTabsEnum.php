<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 24 Jan 2024 14:52:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum FulfilmentTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case DASHBOARD                       = 'dashboard';


    public function blueprint(): array
    {
        return match ($this) {
            FulfilmentTabsEnum::DASHBOARD => [
                'title' => __('dashboard'),
                'icon'  => 'fal fa-tachometer-alt',
            ],


        };
    }
}
