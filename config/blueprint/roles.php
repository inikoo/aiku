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
            'organisation',
            'users',
            'employees',
            'assets',
            'inventory',
            'warehouses',
        ],

        'system-admin'          => [
            'users',
            'look-and-field',
        ],


        'human-resources-clerk' => [
            'employees.view',
            'employees.edit',
            'employees.payroll',
            'employees.attendance',
        ],
        'human-resources-admin' => [
            'employees.view',
            'employees.edit',
            'employees.payroll',
            'employees.attendance',
        ],

        'distribution-admin'             => [
            'inventory',
            'warehouses',
        ],
        'distribution-clerk'             => [
            'inventory.stocks',
            'warehouses.view',
            'warehouses.stock',
        ],
        'distribution-dispatcher-admin'  => [

            'inventory.stocks.view',
            'warehouses.view',
            'warehouses.dispatching',
        ],
        'distribution-dispatcher-picker' => [

            'inventory.stocks.view',
            'warehouses.view',
            'warehouses.dispatching.pick',
        ],
        'distribution-dispatcher-packer' => [

            'inventory.stocks.view',
            'warehouses.view',
            'warehouses.dispatching.pack',
        ],

        'guest'      => [
            'warehouses.view',
        ],

    ];


