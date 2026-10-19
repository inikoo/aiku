<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 02 May 2024 19:19:04 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Procurement;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum OrgAgentTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE              = 'showcase';
    case MONEY                 = 'money';
    case AGREED_TERMS = 'agreed_terms';
    case NOTES = 'notes';
    case INBOX                 = 'inbox';
    case SYSTEM_USERS          = 'system_users';
    case ATTACHMENTS           = 'attachments';
    case HISTORY               = 'history';
    case DATA                  = 'data';
    case IMAGES                = 'images';


    public function blueprint(): array
    {
        return match ($this) {
            OrgAgentTabsEnum::DATA => [
                'title' => __('Data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            OrgAgentTabsEnum::IMAGES => [
                'title' => __('Images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon',
                'align' => 'right',
            ],
            OrgAgentTabsEnum::MONEY => [
                'title' => __('Money'),
                'icon'  => 'fal fa-wallet',
            ],
            OrgAgentTabsEnum::INBOX => [
                'title' => __('Inbox'),
                'icon'  => 'fal fa-inbox',
            ],
            OrgAgentTabsEnum::SYSTEM_USERS => [
                'title' => __('System User'),
                'icon'  => 'fal fa-terminal',
            ],
            OrgAgentTabsEnum::ATTACHMENTS => [
                'title' => __('Attachments'),
                'icon'  => 'fal fa-paperclip',
                'type'  => 'icon',
                'align' => 'right',
            ],
            OrgAgentTabsEnum::HISTORY => [
                'title' => __('History'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            OrgAgentTabsEnum::AGREED_TERMS => [
                'title' => __('Agreed terms'),
                'icon'  => 'fal fa-handshake',
            ],
            OrgAgentTabsEnum::NOTES => [
                'title' => __('Notes'),
                'icon'  => 'fal fa-sticky-note',
            ],
            OrgAgentTabsEnum::SHOWCASE => [
                'title' => __('Agent'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
