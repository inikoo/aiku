<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 09:28:41 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\OMS;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum OrderTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE                    = 'showcase';
    case HISTORY_NOTES               = 'history_notes';
    case ITEMS                       = 'items';
    case ALL_PRODUCTS                = 'all_products';
    case PAYMENTS                    = 'payments';
    case DISCOUNTS                   = 'discounts';
    case INVOICES                    = 'invoices';
    case DELIVERY_NOTES              = 'delivery_notes';

    case HISTORY                     = 'history';

    case DATA                        = 'data';
    case ATTACHMENTS                 = 'attachments';

    case SENT_EMAILS                 = 'sent_emails';






    public function blueprint(): array
    {
        return match ($this) {
            OrderTabsEnum::HISTORY_NOTES => [
                'title' => __('History, Notes'),
                'icon'  => 'fal fa-sticky-note',
            ],
            OrderTabsEnum::ITEMS => [
                'title' => __('items'),
                'icon'  => 'fal fa-bars',
            ],OrderTabsEnum::ALL_PRODUCTS => [
                'title' => __('all products'),
                'icon'  => 'fal fa-th-list',
            ],
            OrderTabsEnum::PAYMENTS => [
                'title' => __('payments'),
                'icon'  => 'fal fa-dollar-sign',
            ],
            OrderTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            OrderTabsEnum::SENT_EMAILS => [
                'title' => __('sent emails'),
                'icon'  => 'fal fa-envelope',
                'type'  => 'icon',
                'align' => 'right',

            ],
            OrderTabsEnum::DISCOUNTS => [
                'title' => __('discounts'),
                'icon'  => 'fal fa-tag',
                'type'  => 'icon-only'

            ],
            OrderTabsEnum::INVOICES => [
                'title' => __('invoices'),
                'icon'  => 'fal fa-file-invoice-dollar',
                'type'  => 'icon-only'

            ],
            OrderTabsEnum::DELIVERY_NOTES => [
                'title' => __('delivery notes'),
                'icon'  => 'fal fa-truck',
                'type'  => 'icon-only'
            ],
            OrderTabsEnum::ATTACHMENTS => [
                'title' => __('attachments'),
                'icon'  => 'fal fa-paperclip',
                'type'  => 'icon',
                'align' => 'right',
            ],OrderTabsEnum::HISTORY => [
                'title' => __('history'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right',
            ],
            OrderTabsEnum::SHOWCASE => [
                'title' => __('order'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
