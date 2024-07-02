<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 20:45:45 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Mail;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum OutboxTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE = 'showvase';



    public function blueprint(): array
    {
        return match ($this) {
            OutboxTabsEnum::SHOWCASE => [
                'title' => __('outbox'),
                'icon'  => 'fas fa-info-circle',
            ],

        };
    }
}
