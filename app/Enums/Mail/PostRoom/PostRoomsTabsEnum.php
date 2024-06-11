<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jun 2024 19:39:10 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Mail\PostRoom;

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
                'title' => __('dispatched_emails'),
                'icon'  => 'fal fa-envelope',
            ],

        };
    }
}
