<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 May 2024 23:17:08 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\UI\SupplyChain;

use App\Enums\EnumHelperTrait;
use App\Enums\HasTabs;

enum SupplierTabsEnum: string
{
    use EnumHelperTrait;
    use HasTabs;


    case SHOWCASE           = 'showcase';
    case PURCHASES_SALES    = 'purchase_sales';
    case SUPPLIER_PRODUCTS  = 'supplier_products';
    case PURCHASE_ORDERS    = 'purchase_orders';
    case DELIVERIES         = 'deliveries';
    case HISTORY            = 'history';


    case SYSTEM_USERS       = 'system_users';

    case ATTACHMENTS        = 'attachments';
    case IMAGES             = 'images';
    case ISSUES             = 'issues';




    public function blueprint(): array
    {
        return match ($this) {

            SupplierTabsEnum::PURCHASES_SALES => [
                'title' => __('purchases/sales'),
                'icon'  => 'fal fa-money-bill',
            ],
            SupplierTabsEnum::SUPPLIER_PRODUCTS => [
                'title' => __('products'),
                'icon'  => 'fal fa-box-usd',
            ],

            SupplierTabsEnum::PURCHASE_ORDERS => [
                'title' => __('purchase orders'),
                'icon'  => 'fal fa-clipboard',
            ],
            SupplierTabsEnum::DELIVERIES => [
                'title' => __('deliveries'),
                'icon'  => 'fal fa-truck',
            ],

            SupplierTabsEnum::SHOWCASE => [
                'title' => __('supplier'),
                'icon'  => 'fas fa-info-circle',
            ],


            SupplierTabsEnum::ISSUES => [
                'title' => __('issues'),
                'icon'  => 'fal fa-poop',
                'type'  => 'icon',
                'align' => 'right',
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
