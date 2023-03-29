<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 20 Mar 2023 14:46:07 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Enums\UI;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum SupplierTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case PURCHASES_SALES    = 'purchase_sales';
    case SUPPLIER_PRODUCTS  = 'supplier_products';
    case ISSUES             = 'issues';
    case PURCHASE_ORDERS    = 'purchase_orders';
    case DELIVERIES         = 'deliveries';
    case HISTORY            = 'history';

    case DATA               = 'data';

    case SYSTEM_USERS       = 'system_users';

    case ATTACHMENTS        = 'attachments';
    case IMAGES             = 'images';




    public function blueprint(): array
    {
        return match ($this) {
            SupplierTabsEnum::DATA => [
                'title' => __('data'),
                'icon'  => 'fal fa-database',
                'type'  => 'icon',
                'align' => 'right',
            ],
            SupplierTabsEnum::PURCHASES_SALES => [
                'title' => __('purchases/sales'),
                'icon'  => 'fal fa-money-bill',
            ],
            SupplierTabsEnum::SUPPLIER_PRODUCTS => [
                'title' => __('supplier/products'),
                'icon'  => 'fal fa-hand-receiving',
            ],
            SupplierTabsEnum::ISSUES => [
                'title' => __('issues'),
                'icon'  => 'fal fa-poop',
            ],
            SupplierTabsEnum::PURCHASE_ORDERS => [
                'title' => __('purchase/orders'),
                'icon'  => 'fal fa-clipboard',
            ],
            SupplierTabsEnum::DELIVERIES => [
                'title' => __('deliveries'),
                'icon'  => 'fal fa-truck',
            ],

            SupplierTabsEnum::IMAGES => [
                'title' => __('images'),
                'icon'  => 'fal fa-camera-retro',
                'type'  => 'icon',
                'align' => 'right',
            ],
            SupplierTabsEnum::ATTACHMENTS => [
                'title' => __('attachments'),
                'icon'  => 'fal fa-paperclip',
                'type'  => 'icon',
                'align' => 'right',
            ],
            SupplierTabsEnum::SYSTEM_USERS => [
                'title' => __('system/users'),
                'icon'  => 'fal fa-paper-plane',
                'type'  => 'icon',
                'align' => 'right',
            ],SupplierTabsEnum::HISTORY => [
                'title' => __('changelog'),
                'icon'  => 'fal fa-clock',
                'type'  => 'icon',
                'align' => 'right'
            ],
        };
    }
}
