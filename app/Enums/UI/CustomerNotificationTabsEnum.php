<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum CustomerNotificationTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SETTINGS                               = 'setting';
    case NOTIFICATIONS_TO_BE_SEND_NEXT_SHOT     = 'notifications_to_be_send_next_shot';
    case WORKSHOP                               = 'workshop';

    case MAILSHOTS                              = 'mailshots';
    case SENT_EMAILS                            = 'sent_emails';

    case CHANGELOG                              = 'changelog';


    public function blueprint(): array
    {
        return match ($this) {
            CustomerNotificationTabsEnum::SETTINGS => [
                'title' => __('setting'),
                'icon'  => 'fal fa-sliders-h',
            ],
            CustomerNotificationTabsEnum::NOTIFICATIONS_TO_BE_SEND_NEXT_SHOT => [
                'title' => __('notifications to be send next shot'),
                'icon'  => 'fal fa-user-clock',
            ],
            CustomerNotificationTabsEnum::WORKSHOP => [
                'title' => __('workshop'),
                'icon'  => 'fal fa-wrench',
            ],
            CustomerNotificationTabsEnum::MAILSHOTS => [
                'title' => __('mailshots'),
                'icon'  => 'fal fa-container-storage',
                'type'  => 'icon-only',
            ],
            CustomerNotificationTabsEnum::SENT_EMAILS => [
                'title' => __('sent emails'),
                'icon'  => 'fal fa-paper-plane',
                'type'  => 'icon-only',
            ],
            CustomerNotificationTabsEnum::CHANGELOG => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-road',
                'type'  => 'icon-only',
            ],
        };
    }
}
