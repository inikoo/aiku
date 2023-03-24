<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum OrderTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case ITEMS                       = 'items';
    case ALL_PRODUCTS                = 'all_products';
    case CUSTOMER_NOTES_HISTORY      = 'customer_notes_history';
    case PAYMENTS                    = 'payments';
    case DATA                        = 'data';
    case SENT_EMAILS                 = 'sent_emails';
    case DISCOUNTS                   = 'discounts';
    case INVOICES                    = 'invoices';
    case DELIVERY_NOTES              = 'delivery_notes';
    case ATTACHMENTS                 = 'attachments';

    case CHANGELOG                  = 'changelog';


    public function blueprint(): array
    {
        return match ($this) {
            OrderTabsEnum::ITEMS => [
                'title' => __('items'),
                'icon'  => 'fal fa-bars',
            ],OrderTabsEnum::ALL_PRODUCTS => [
                'title' => __('all products'),
                'icon'  => 'fal fa-th-list',
            ],
            OrderTabsEnum::CUSTOMER_NOTES_HISTORY => [
                'title' => __('Customer notes/history'),
                'icon'  => 'fal fa-user-tag',
            ],
            OrderTabsEnum::PAYMENTS => [
                'title' => __('payments'),
                'icon'  => 'fal fa-dollar-sign',
            ],
            OrderTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-envelope',
                'type'  => 'icon-only'
            ],
            OrderTabsEnum::SENT_EMAILS => [
                'title' => __('sent emails'),
                'icon'  => 'fal fa-envelope',
                'type'  => 'icon-only'

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
                'icon'  => 'fal fa-paperclip',
                'type'  => 'icon-only'
            ],
            OrderTabsEnum::ATTACHMENTS => [
                'title' => __('attachments'),
                'icon'  => 'fal fa-paperclip',
                'type'  => 'icon-only',
            ],OrderTabsEnum::CHANGELOG => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-road',
                'type'  => 'icon-only',
            ],
        };
    }
}
