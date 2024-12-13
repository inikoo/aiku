<?php
/*
 * author Arya Permana - Kirin
 * created on 13-12-2024-10h-51m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Enums\UI\Ordering;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum OrdersBacklogTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case CREATING                       = 'creating';
    case SUBMITTED                       = 'submitted';
    case IN_WAREHOUSE                       = 'in_warehouse';
    case HANDLING                       = 'handling';
    case HANDLING_BLOCKED                       = 'handling_blocked';
    case PACKED                       = 'packed';
    case FINALISED                       = 'finalised';
    case DISPATCHED_TODAY                       = 'dispatched_today';





    // public function blueprint(): array
    // {
    //     return match ($this) {

    //         OrderTabsEnum::TRANSACTIONS => [
    //             'title' => __('transactions'),
    //             'icon'  => 'fal fa-bars',
    //         ],
    //         // OrderTabsEnum::PAYMENTS => [
    //         //     'type'  => 'icon',
    //         //     'align' => 'right',
    //         //     'title' => __('payments'),
    //         //     'icon'  => 'fal fa-dollar-sign',
    //         // ],

    //         // OrderTabsEnum::SENT_EMAILS => [
    //         //     'title' => __('sent emails'),
    //         //     'icon'  => 'fal fa-envelope',
    //         //     'type'  => 'icon',
    //         //     'align' => 'right',

    //         // ],
    //         // OrderTabsEnum::DISCOUNTS => [
    //         //     'title' => __('discounts'),
    //         //     'icon'  => 'fal fa-tag',
    //         //     'type'  => 'icon',
    //         //     'align' => 'right',

    //         // ],
    //         OrderTabsEnum::INVOICES => [
    //             'title' => __('invoices'),
    //             'icon'  => 'fal fa-file-invoice-dollar',
    //             'type'  => 'icon',
    //             'align' => 'right',

    //         ],
    //         OrderTabsEnum::DELIVERY_NOTES => [
    //             'title' => __('delivery notes'),
    //             'icon'  => 'fal fa-truck',
    //             'type'  => 'icon',
    //             'align' => 'right',
    //         ],
    //         OrderTabsEnum::ATTACHMENTS => [
    //             'title' => __('attachments'),
    //             'icon'  => 'fal fa-paperclip',
    //             'type'  => 'icon',
    //             'align' => 'right',
    //         ],
    //         //OrderTabsEnum::HISTORY => [
    //         //     'title' => __('history'),
    //         //     'icon'  => 'fal fa-clock',
    //         //     'type'  => 'icon',
    //         //     'align' => 'right',
    //         // ],

    //     };
    // }
}
