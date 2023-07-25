<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 Mar 2023 01:54:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum StoredItemTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE = 'showcase';

    public function blueprint(): array
    {
        return match ($this) {
            StoredItemTabsEnum::SHOWCASE => [
                'title' => __('stored item'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
