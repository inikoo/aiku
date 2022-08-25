<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 03 Feb 2022 18:34:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

return [


    'positions' => [

        'dir'   => [
            'slug' => 'dir',
            'name' => 'director',

            'roles' => [
                'super-admin'
            ]
        ],
        'hr-m'  => [
            'slug'  => 'hr-m',
            'grade' => 'manager',
            'name'  => 'Human resources supervisor',
            'roles' => [
                'human-resources-admin'
            ]
        ],
        'hr-c'  => [
            'slug'  => 'hr-c',
            'name'  => 'Human resources clerk',
            'grade' => 'clerk',
            'roles' => [
                'human-resources-clerk'
            ]
        ],
        'acc'   => [
            'slug'  => 'acc',
            'name'  => 'Accounts',
            'roles' => [
                'guest'
            ]
        ],
        'mrk-m' => [
            'slug'  => 'mrk-m',
            'grade' => 'manager',
            'name'  => 'Marketing supervisor',
            'roles' => [
                'guest'
            ]
        ],
        'mrk-c' => [
            'slug'  => 'mrk-c',
            'grade' => 'clerk',
            'name'  => 'Marketing clerk',
            'roles' => [
                'guest'
            ]
        ],
        'web-m' => [
            'slug'  => 'web-m',
            'grade' => 'manager',
            'name'  => 'Webmaster supervisor',
            'roles' => [
                'guest'
            ]
        ],
        'web-c' => [
            'slug'  => 'web-c',
            'grade' => 'clerk',
            'name'  => 'Webmaster clerk',
            'roles' => [
                'guest'
            ]
        ],
        'buy'      => [
            'slug'  => 'buy',
            'name'  => 'Buyer',
            'roles' => [
                'guest'
            ]
        ],
        'wah-m'    => [
            'slug'       => 'wah-m',
            'team'       => 'warehouse',
            'department' => 'procurement',
            'name'       => 'Warehouse supervisor',
            'roles'      => [
                'distribution-admin'
            ]
        ],
        'wah-sk'   => [
            'slug'       => 'wah-sk',
            'team'       => 'warehouse',
            'department' => 'warehouse',

            'name'  => 'Warehouse stock keeper',
            'roles' => [
                'guest'
            ]
        ],
        'wah-sc'   => [
            'slug'       => 'wah-sc',
            'name'       => 'Stock Controller',
            'team'       => 'warehouse',
            'department' => 'warehouse',
            'roles'      => [
                'distribution-clerk'
            ]
        ],
        'dist-m'   => [
            'slug'       => 'dist-m',
            'name'       => 'Dispatch supervisor',
            'team'       => 'warehouse',
            'department' => 'warehouse',
            'roles'      => [
                'distribution-dispatcher-admin'
            ]
        ],
        'dist-pik' => [
            'slug'       => 'dist-pik',
            'team'       => 'warehouse',
            'department' => 'warehouse',
            'name'       => 'Picker',
            'roles'      => [
                'distribution-dispatcher-picker'
            ]
        ],
        'dist-pak' => [
            'slug'       => 'dist-pak',
            'team'       => 'warehouse',
            'department' => 'warehouse',
            'name'       => 'Packer',
            'roles'      => [
                'distribution-dispatcher-packer'
            ]
        ],
        'prod-m'   => [
            'slug'       => 'prod-m',
            'team'       => 'production',
            'department' => 'production',
            'name'       => 'Production supervisor',
            'roles'      => [
                'guest'
            ]
        ],
        'prod-w'   => [
            'slug'       => 'prod-w',
            'team'       => 'production',
            'department' => 'production',
            'name'       => 'Production operative',
            'roles'      => [
                'guest'
            ]
        ],
        'cus-m'    => [
            'slug'  => 'cus-m',
            'grade' => 'manager',
            'name'  => 'Customer service supervisor',
            'roles' => [
                'guest'
            ]
        ],
        'cus-c'    => [
            'slug'  => 'cus-c',
            'grade' => 'clerk',
            'name'  => 'Customer service',
            'roles' => [
                'guest'
            ]
        ],
    ],
    'wrappers'  => [
        'hr'  => ['hr-m', 'hr-c'],
        'mrk' => ['mrk-m', 'mrk-c'],
        'cus' => ['cus-m', 'cus-c']
    ],

    'blueprint' => [
        'management' => [
            'title'     => 'management and operations',
            'positions' => [
                'dir' => 'dir',
                'acc' => 'acc',
                'buy' => 'buy',
                'hr'  => ['hr-m', 'hr-c'],


            ]
        ],
        'marketing'  => [
            'title'     => 'marketing and customer services',
            'positions' => [
                'mrk' => ['mrk-m', 'mrk-c'],
                'cus' => ['cus-m', 'cus-c'],


            ],
            'scope'     => 'shops'
        ],
        'inventory'  => [
            'title'     => 'warehousing',
            'positions' => [


            ],
            'scope'     => 'warehouses'
        ],

    ]
];
