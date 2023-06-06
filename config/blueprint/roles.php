<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 24 Aug 2022 14:50:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */


return
    [
        'super-admin'           => [
            'tenant',
            'showroom',
            'customers',
            'sysadmin',
            'hr',
            'inventory',
            'dispatch',
            'shops',
            'osm',
            'production',
            'procurement',
            'fulfilment',
            'accounting',
            'shops',
            'mail'
        ],

        'system-admin'          => [
            'sysadmin',
            'look-and-field',
        ],


        'human-resources-clerk' => [
            'hr.view',
            'hr.edit',
            'hr.payroll',
            'hr.attendance',
        ],
        'human-resources-admin' => [
            'hr.view',
            'hr.edit',
            'hr.payroll',
            'hr.attendance',
        ],

        'distribution-admin'             => [
            'inventory',
            'fulfilment'
        ],
        'distribution-clerk'             => [
            'inventory.stocks',
            'inventory.warehouses.view',
            'inventory.warehouses.stock',
            'fulfilment.view'
        ],
        'distribution-dispatcher-admin'  => [

            'inventory.stocks.view',
            'inventory.warehouses.view',
            'dispatch',
        ],
        'distribution-dispatcher-picker' => [

            'inventory.stocks.view',
            'inventory.warehouses.view',
            'dispatch.pick',
        ],
        'distribution-dispatcher-packer' => [

            'inventory.stocks.view',
            'inventory.warehouses.view',
            'dispatch.pack',
        ],

        'shop-manager' => [
            'shops',
        ],

        'customer-services' => [
            'shops.view',
            'customers',
            'osm'
        ],

        'webmaster' => [
            'shops.view',
            'shops.websites'
        ],

        'production-manager' => [
            'production.view',
        ],

        'production-worker' => [
            'production.view',
        ],

        'procurement' => [
            'procurement',
        ],


        'guest'      => [

        ],

    ];
