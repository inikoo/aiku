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
            'sysadmin',
            'hr',
            'assets',
            'inventory',
            'dispatching',
            'shops'
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
        ],
        'distribution-clerk'             => [
            'inventory.stocks',
            'inventory.warehouses.view',
            'inventory.warehouses.stock',
        ],
        'distribution-dispatcher-admin'  => [

            'inventory.stocks.view',
            'inventory.warehouses.view',
            'dispatching',
        ],
        'distribution-dispatcher-picker' => [

            'inventory.stocks.view',
            'inventory.warehouses.view',
            'dispatching.pick',
        ],
        'distribution-dispatcher-packer' => [

            'inventory.stocks.view',
            'inventory.warehouses.view',
            'dispatching.pack',
        ],

        'shop-manager' => [
            'shops',
        ],

        'customer-services' => [
            'shops.view',
            'shops.customers',
        ],


        'guest'      => [

        ],

    ];


