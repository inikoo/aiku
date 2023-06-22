<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Jun 2023 20:10:15 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum MailroomsTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case MAILROOMS          = 'mailrooms';
    case OUTBOXES           = 'outboxes';
    case MAILSHOTS          = 'mailshots';
    case DISPATCHED_EMAILS  = 'dispatched_emails';

    public function blueprint(): array
    {
        return match ($this) {
            MailroomsTabsEnum::MAILROOMS => [
                'title' => __('mailrooms'),
                'icon'  => 'fal fa-mailbox',
            ],
            MailroomsTabsEnum::OUTBOXES => [
                'title' => __('outboxes'),
                'icon'  => 'fal fa-inbox-out',
            ],
            MailroomsTabsEnum::MAILSHOTS => [
                'title' => __('mailshots'),
                'icon'  => 'fal fa-folder',
            ],
            MailroomsTabsEnum::DISPATCHED_EMAILS => [
                'title' => __('dispatched_emails'),
                'icon'  => 'fal fa-envelope',
            ],

        };
    }
}
