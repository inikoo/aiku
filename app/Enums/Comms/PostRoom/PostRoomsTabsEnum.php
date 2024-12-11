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

    case POST_ROOMS          = 'post_rooms';
    case OUTBOXES            = 'outboxes';
    case MAILSHOTS           = 'mailshots';
    case DISPATCHED_EMAILS   = 'dispatched_emails';

    public function blueprint(): array
    {
        return match ($this) {
            PostRoomsTabsEnum::POST_ROOMS => [
                'title' => __('post rooms'),
                'icon'  => 'fal fa-mailbox',
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
