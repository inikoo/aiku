<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 24 Aug 2022 14:50:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */


return
    [
        'super-admin' => [
            'tenant',
            'business_intelligence',
            'shops',
            'websites',
            'crm',
            'marketing',
            'dispatch',
            'inventory',
            'production',
            'procurement',
            'accounting',
            'hr',
            'sysadmin',
            'fulfilment',
        ],

        'system-admin' => [
            'sysadmin',
        ],


        'human-resources-clerk'   => [
            'hr.view',
            'hr.edit',
            'hr.payroll',
            'hr.attendance',
        ],
        'human-resources-manager' => [
            'hr.view',
            'hr.edit',
            'hr.payroll',
            'hr.attendance',
        ],

        'distribution-manager'            => [
            'inventory',
            'fulfilment'
        ],
        'distribution-clerk'              => [
            'inventory.stocks',
            'inventory.warehouses.view',
            'inventory.warehouses.stock',
            'fulfilment.view'
        ],
        'distribution-dispatcher-manager' => [

            'inventory.stocks.view',
            'inventory.warehouses.view',
            'dispatch',
        ],
        'distribution-dispatcher-picker'  => [

            'inventory.stocks.view',
            'inventory.warehouses.view',
            'dispatch.pick',
        ],
        'distribution-dispatcher-packer'  => [

            'inventory.stocks.view',
            'inventory.warehouses.view',
            'dispatch.pack',
        ],

        'shop-manager' => [
            'shops',
        ],

        'shop-clerk' => [
            'shops',
        ],

        'customer-services-clerk'   => [
            'shops.view',
            'crm',
        ],
        'customer-services-manager' => [
            'shops.view',
            'crm',
        ],

        'accountant-clerk' => [
            'accounting',
        ],

        'accountant-manager' => [
            'accounting',
        ],

        'webmaster-clerk' => [
            'shops.view',
            'websites'
        ],

        'webmaster-manager' => [
            'shops.view',
            'websites'
        ],

        'production-manager' => [
            'production',
        ],

        'production-worker' => [
            'production',
        ],

        'procurement-manager' => [
            'procurement',
        ],

        'procurement-clerk' => [
            'procurement',
        ],

        'business-intelligence-analyst' => [

        ],

        'marketing-broadcaster-clerk' => [

        ],

        'marketing-broadcaster-manager' => [

        ],

        'guest' => [

        ],

        'fulfilment-manager' => [
            'fulfilment'
        ],

        'fulfilment-worker' => [
            'fulfilment'
        ],


    ];
