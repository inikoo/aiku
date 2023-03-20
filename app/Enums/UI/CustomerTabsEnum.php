<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum CustomerTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;

    case CHANGELOG           = 'changelog';

    case DATA                = 'data';
    case PORTFOLIO           = 'portfolio';
    case PRODUCTS            = 'products';
    case ORDERS              = 'orders';
    case SALES               = 'sales';
    case INSIGHTS            = 'insights';
    case DISCOUNTS           = 'discounts';
    case CREDITS             = 'credits';
    case ATTACHMENTS         = 'attachments';
    case DISPATCHED_EMAILS   = 'dispatched_emails';



    public function blueprint(): array
    {
        return match ($this) {
            CustomerTabsEnum::CHANGELOG     => [
                'title' => __('history , notes'),
                'icon'  => 'fal fa-sticky-note',
            ],
            CustomerTabsEnum::DATA     => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
            ],
            CustomerTabsEnum::PORTFOLIO             => [
                'title' => __('portfolio'),
                'icon'  => 'fal fa-store-alt',
            ],
            CustomerTabsEnum::PRODUCTS => [
                'title' => __('warehouse areas'),
                'icon'  => 'fal fa-cube',
            ],
            CustomerTabsEnum::ORDERS     => [
                'title' => __('locations'),
                'icon'  => 'fal fa-shopping-cart',
            ],CustomerTabsEnum::SALES     => [
                'title' => __('sales'),
                'icon'  => 'fal fa-money-bill',
            ],CustomerTabsEnum::INSIGHTS     => [
                'title' => __('insights'),
                'icon'  => 'fal fa-graduation-cap',
            ],CustomerTabsEnum::DISCOUNTS     => [
                'title' => __('discounts'),
                'icon'  => 'fal fa-tags',
            ],CustomerTabsEnum::CREDITS     => [
                'title' => __('credit blockchains'),
                'icon'  => 'fal fa-code-commit',
            ],CustomerTabsEnum::ATTACHMENTS     => [
                'title' => __('attachments'),
                'icon'  => 'fal fa-paperclip',
                'type'  => 'icon-only'
            ],CustomerTabsEnum::DISPATCHED_EMAILS     => [
                'title' => __('dispatched emails'),
                'icon'  => 'fal fa-paper-plane',
                'type'  => 'icon-only',
            ],
        };
    }
}
