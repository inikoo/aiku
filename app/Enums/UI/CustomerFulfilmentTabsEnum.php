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

    case PALLETS       = 'pallets';
    case STORED_ITEMS  = 'stored_items';

    case STORED_ITEM_RETURNS        = 'stored_item_returns';
    case PALLET_DELIVERIES          = 'pallet_deliveries';

    case PALLET_RETURNS        = 'pallet_returns';
    case INVOICES              = 'invoices';

    case DATA              = 'data';
    case ATTACHMENTS       = 'attachments';
    case DISPATCHED_EMAILS = 'dispatched_emails';

    case WEB_USERS = 'web_users';


    public function blueprint(): array
    {
        return match ($this) {
            CustomerFulfilmentTabsEnum::PALLETS => [
                'title' => __('pallets'),
                'icon'  => 'fal fa-pallet',
            ],
            CustomerFulfilmentTabsEnum::STORED_ITEMS => [
                'title' => __('stored items'),
                'icon'  => 'fal fa-narwhal',
            ],
            CustomerFulfilmentTabsEnum::STORED_ITEM_RETURNS => [
                'title' => __('stored item returns'),
                'icon'  => 'fal fa-truck-loading',
            ],
            CustomerFulfilmentTabsEnum::DATA => [
                'align' => 'right',
                'type'  => 'icon',
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            CustomerFulfilmentTabsEnum::INVOICES => [
                'title' => __('invoices'),
                'icon'  => 'fal fa-file-invoice-dollar',
            ],
            CustomerFulfilmentTabsEnum::PALLET_DELIVERIES => [
                'title' => __('deliveries'),
                'icon'  => 'fal fa-truck-couch',
            ],
            CustomerFulfilmentTabsEnum::PALLET_RETURNS => [
                'title' => __('returns'),
                'icon'  => 'fal fa-sign-out-alt',
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
            CustomerFulfilmentTabsEnum::WEB_USERS => [
                'align' => 'right',
                'title' => __('users'),
                'icon'  => 'fal fa-terminal',
                'type'  => 'icon',
            ],
            CustomerFulfilmentTabsEnum::SHOWCASE => [
                'title' => __('customer'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
