<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:16:37 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\CRM;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum CustomerClientTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE = 'showcase';

    case TIMELINE  = 'timeline';
    // case PORTFOLIO = 'portfolio';
    // case ORDERS    = 'orders';

    case DATA                        = 'data';
    case ATTACHMENTS                 = 'attachments';
    case DISPATCHED_EMAILS           = 'dispatched_emails';
    case WEB_USERS                   = 'web_users';
    case CREDIT_TRANSACTIONS         = 'credit_transactions';


    public function blueprint(): array
    {
        return match ($this) {
            CustomerClientTabsEnum::TIMELINE => [
                'title' => __('timeline'),
                'icon'  => 'fal fa-code-branch',
            ],
            CustomerClientTabsEnum::DATA => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            // CustomerTabsEnum::PORTFOLIO => [
            //     'title' => __('portfolio'),
            //     'icon'  => 'fal fa-store-alt',
            // ],

            // CustomerTabsEnum::ORDERS => [
            //     'title' => __('orders'),
            //     'icon'  => 'fal fa-shopping-cart',
            // ],
            CustomerClientTabsEnum::ATTACHMENTS => [
                'align' => 'right',
                'title' => __('attachments'),
                'icon'  => 'fal fa-paperclip',
                'type'  => 'icon'
            ],
            CustomerClientTabsEnum::DISPATCHED_EMAILS => [
                'align' => 'right',
                'title' => __('dispatched emails'),
                'icon'  => 'fal fa-paper-plane',
                'type'  => 'icon',
            ],
            CustomerClientTabsEnum::SHOWCASE => [
                'title' => __('customer'),
                'icon'  => 'fas fa-info-circle',
            ],
            CustomerClientTabsEnum::WEB_USERS => [
                'align' => 'right',
                'title' => __('users'),
                'icon'  => 'fal fa-globe',
                'type'  => 'icon',
            ],
            CustomerClientTabsEnum::CREDIT_TRANSACTIONS => [
                'align' => 'right',
                'title' => __('credit transactions'),
                'icon'  => 'fal fa-abacus',
                'type'  => 'icon',
            ],
        };
    }
}
