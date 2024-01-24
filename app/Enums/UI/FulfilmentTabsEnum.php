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

    case FULFILMENTS                       = 'fulfilments';


    public function blueprint(): array
    {
        return match ($this) {
            FulfilmentTabsEnum::FULFILMENTS => [
                'title' => __('fulfilment shops'),
                'icon'  => 'fal fa-store-alt',
            ],


        };
    }
}
