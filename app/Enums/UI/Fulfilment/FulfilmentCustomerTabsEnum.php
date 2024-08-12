<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 15:16:47 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\Fulfilment;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum FulfilmentCustomerTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE      = 'showcase';
    case AGREED_PRICES = 'agreed_prices';
    case HISTORY       = 'history';
    case ATTACHMENTS   = 'attachments';
    case WEBHOOK       = 'webhook';


    public function blueprint(): array
    {
        return match ($this) {
            FulfilmentCustomerTabsEnum::ATTACHMENTS => [
                'align' => 'right',
                'title' => __('attachments'),
                'icon'  => 'fal fa-paperclip',
                'type'  => 'icon'
            ],
            FulfilmentCustomerTabsEnum::HISTORY => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
            ],

            FulfilmentCustomerTabsEnum::WEBHOOK => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('webhook'),
                'icon'  => 'fal fa-clipboard-list-check',
            ],
            FulfilmentCustomerTabsEnum::SHOWCASE => [
                'title' => __('customer'),
                'icon'  => 'fas fa-info-circle',
            ],
            FulfilmentCustomerTabsEnum::AGREED_PRICES => [
                'title' => __('agreed prices'),
                'icon'  => 'fal fa-usd-circle',
            ],
        };
    }
}
