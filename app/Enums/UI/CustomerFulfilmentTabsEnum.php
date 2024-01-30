<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum CustomerFulfilmentTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case SHOWCASE = 'showcase';

    case STORED_ITEMS  = 'stored_items';
    case ORDERS        = 'orders';

    case PALLET_DELIVERIES        = 'pallet_deliveries';

    case DATA              = 'data';
    case ATTACHMENTS       = 'attachments';
    case DISPATCHED_EMAILS = 'dispatched_emails';


    public function blueprint(): array
    {
        return match ($this) {
            CustomerFulfilmentTabsEnum::STORED_ITEMS => [
                'title' => __('stored items'),
                'icon'  => 'fal fa-narwhal',
            ],
            CustomerFulfilmentTabsEnum::DATA => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            CustomerFulfilmentTabsEnum::ORDERS => [
                'title' => __('orders'),
                'icon'  => 'fal fa-shopping-cart',
            ],
            CustomerFulfilmentTabsEnum::PALLET_DELIVERIES => [
                'title' => __('pallet deliveries'),
                'icon'  => 'fal fa-truck',
            ],
            CustomerFulfilmentTabsEnum::ATTACHMENTS => [
                'align' => 'right',
                'title' => __('attachments'),
                'icon'  => 'fal fa-paperclip',
                'type'  => 'icon'
            ],
            CustomerFulfilmentTabsEnum::DISPATCHED_EMAILS => [
                'align' => 'right',
                'title' => __('dispatched emails'),
                'icon'  => 'fal fa-paper-plane',
                'type'  => 'icon',
            ],
            CustomerFulfilmentTabsEnum::SHOWCASE => [
                'title' => __('customer'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
