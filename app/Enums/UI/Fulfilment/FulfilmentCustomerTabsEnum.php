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

    /*
     case PALLETS       = 'pallets';
     case STORED_ITEMS  = 'stored_items';

     case PALLET_DELIVERIES                = 'pallet_deliveries';
     case STORED_ITEM_RETURNS              = 'stored_item_returns';
     case PALLET_RETURNS                   = 'pallet_returns';

     case RECURRING_BILLS                  = 'recurring_bills';
     case INVOICES                         = 'invoices';
*/

    case HISTORY              = 'history';
    case ATTACHMENTS          = 'attachments';

    //   case WEB_USERS = 'web_users';
    case WEBHOOK   = 'webhook';


    public function blueprint(): array
    {
        return match ($this) {
            /*
            FulfilmentCustomerTabsEnum::PALLETS => [
                'title' => __('pallets'),
                'icon'  => 'fal fa-pallet',
            ],
            FulfilmentCustomerTabsEnum::STORED_ITEMS => [
                'title' => __('stored items'),
                'icon'  => 'fal fa-narwhal',
            ],
            FulfilmentCustomerTabsEnum::STORED_ITEM_RETURNS => [
                'title' => __('stored item returns'),
                'icon'  => 'fal fa-truck-loading',
            ],
            FulfilmentCustomerTabsEnum::INVOICES => [
                'title' => __('invoices'),
                'icon'  => 'fal fa-file-invoice-dollar',
            ],
            FulfilmentCustomerTabsEnum::RECURRING_BILLS => [
                'title' => __('recurring bills'),
                'icon'  => 'fal fa-receipt',
            ],
            FulfilmentCustomerTabsEnum::PALLET_DELIVERIES => [
                'title' => __('deliveries'),
                'icon'  => 'fal fa-truck-couch',
            ],
            FulfilmentCustomerTabsEnum::PALLET_RETURNS => [
                'title' => __('returns'),
                'icon'  => 'fal fa-sign-out-alt',
            ],


            FulfilmentCustomerTabsEnum::WEB_USERS => [
                'align' => 'right',
                'title' => __('users'),
                'icon'  => 'fal fa-terminal',
                'type'  => 'icon',
            ],
            */
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
                'icon'  => 'fal fa-network-wired',
            ],
            FulfilmentCustomerTabsEnum::SHOWCASE => [
                'title' => __('customer'),
                'icon'  => 'fas fa-info-circle',
            ],
            FulfilmentCustomerTabsEnum::AGREED_PRICES => [
                'title' => __('agreed prices'),
                'icon'  => 'fas fa-info-circle',
            ],
        };
    }
}
