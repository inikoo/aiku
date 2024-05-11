<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:13:49 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\CRM;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum ProspectsTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case DASHBOARD = 'dashboard';
    case PROSPECTS = 'prospects';

    case HISTORY   = 'history';
    case TAGS      = 'tags';
    case LISTS     = 'lists';
    case MAILSHOTS = 'mailshots';

    public function blueprint(): array
    {
        return match ($this) {
            ProspectsTabsEnum::DASHBOARD => [
                'title' => __('dashboard'),
                'icon'  => 'fal fa-tachometer-alt',
            ],

            ProspectsTabsEnum::PROSPECTS => [
                'title' => __('prospects'),
                'icon'  => 'fal fa-transporter',
            ],

            ProspectsTabsEnum::LISTS => [
                'title' => __('lists'),
                'icon'  => 'fal fa-code-branch',
                'type'  => 'icon',
                'align' => 'right'
            ],

            ProspectsTabsEnum::TAGS => [
                'title' => __('tags'),
                'icon'  => 'fal fa-tags',
                'type'  => 'icon',
                'align' => 'right'
            ],

            ProspectsTabsEnum::MAILSHOTS => [
                'title' => __('mailshots'),
                'icon'  => 'fal fa-mail-bulk',
                'type'  => 'icon',
                'align' => 'right'
            ],

            ProspectsTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right'
            ]
        };
    }
}
