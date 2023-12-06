<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 24 Aug 2022 14:50:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */


return [
    'base'=> [
        'super-admin' => [
            'business-intelligence',
            'shops',
            'websites',
            'crm',
            'oms',
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
        'human-resources-supervisor' => [
            'hr.view',
            'hr.edit',
            'hr.payroll',
            'hr.attendance',
        ],

        'accounting' => [
            'accounting',
        ],

        'accounting-supervisor' => [
            'accounting',
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
            'oms'
        ],
        'customer-services-manager' => [
            'shops.view',
            'crm',
            'oms'
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
            'business-intelligence',
            'crm.view',
            'oms.view'

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


    ],
    'organisation'=> [
        'super-admin' => [
            'business-intelligence',
            'shops',
            'websites',
            'crm',
            'oms',
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
        'human-resources-supervisor' => [
            'hr.view',
            'hr.edit',
            'hr.payroll',
            'hr.attendance',
        ],

        'accounting' => [
            'accounting',
        ],

        'accounting-supervisor' => [
            'accounting',
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
            'oms'
        ],
        'customer-services-manager' => [
            'shops.view',
            'crm',
            'oms'
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
            'business-intelligence',
            'crm.view',
            'oms.view'

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


    ]
];
