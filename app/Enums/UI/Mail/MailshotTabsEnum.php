<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 20:45:45 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Mail;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum MailshotTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE = 'showcase';
    case EMAIL_PREVIEW = 'email_preview';

    public function blueprint(): array
    {
        return match ($this) {
            MailshotTabsEnum::SHOWCASE => [
                'title' => __('Showcase'),
                'icon'  => 'fas fa-info-circle',
            ],
            MailshotTabsEnum::EMAIL_PREVIEW => [
                'title' => __('Email Preview'),
                'icon'  => 'fas fa-envelope',
            ]
        };
    }
}
