<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:10:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Comms\PostRoom;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum PostRoomsTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE          = 'showcase';
    case OUTBOXES            = 'outboxes';
    case MAILSHOTS           = 'mailshots';
    case DISPATCHED_EMAILS   = 'dispatched_emails';

    public function blueprint(): array
    {
        return match ($this) {
            PostRoomsTabsEnum::SHOWCASE => [
                'title' => __('overview'),
                'icon'  => 'fal fa-tachometer-alt-fast',
            ],
            PostRoomsTabsEnum::OUTBOXES => [
                'title' => __('outboxes'),
                'icon'  => 'fal fa-inbox-out',
            ],
            PostRoomsTabsEnum::MAILSHOTS => [
                'title' => __('mailshots'),
                'icon'  => 'fal fa-folder',
            ],
            PostRoomsTabsEnum::DISPATCHED_EMAILS => [
                'title' => __('dispatched emails'),
                'icon'  => 'fal fa-envelope',
            ],

        };
    }
}
