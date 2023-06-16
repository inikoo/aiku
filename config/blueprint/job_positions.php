<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 03 Feb 2022 18:34:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

return [


    'positions' => [

        'dir'      => [
            'code' => 'dir',
            'name' => 'director',

            'roles' => [
                'super-admin'
            ]
        ],
        'hr-m'     => [
            'code'  => 'hr-m',
            'grade' => 'manager',
            'name'  => 'Human resources supervisor',
            'roles' => [
                'human-resources-admin'
            ]
        ],
        'hr-c'     => [
            'code'  => 'hr-c',
            'name'  => 'Human resources clerk',
            'grade' => 'clerk',
            'roles' => [
                'human-resources-clerk'
            ]
        ],
        'acc'      => [
            'code'  => 'acc',
            'name'  => 'Accounts',
            'roles' => [
                'guest'
            ]
        ],
        'mrk-m'    => [
            'code'  => 'mrk-m',
            'grade' => 'manager',
            'name'  => 'Marketing supervisor',
            'roles' => [
                'guest'
            ]
        ],
        'mrk-c'    => [
            'code'  => 'mrk-c',
            'grade' => 'clerk',
            'name'  => 'Marketing clerk',
            'roles' => [
                'guest'
            ]
        ],
        'web-m'    => [
            'code'  => 'web-m',
            'grade' => 'manager',
            'name'  => 'Webmaster supervisor',
            'roles' => [
                'guest'
            ]
        ],
        'web-c'    => [
            'code'  => 'web-c',
            'grade' => 'clerk',
            'name'  => 'Webmaster clerk',
            'roles' => [
                'guest'
            ]
        ],
        'buy'      => [
            'code'  => 'buy',
            'name'  => 'Buyer',
            'roles' => [
                'guest'
            ]
        ],
        'wah-m'    => [
            'code'       => 'wah-m',
            'team'       => 'warehouse',
            'department' => 'procurement',
            'name'       => 'Warehouse supervisor',
            'roles'      => [
                'distribution-admin'
            ]
        ],
        'wah-sk'   => [
            'code'       => 'wah-sk',
            'team'       => 'warehouse',
            'department' => 'warehouse',

            'name'  => 'Warehouse stock keeper',
            'roles' => [
                'guest'
            ]
        ],
        'wah-sc'   => [
            'code'       => 'wah-sc',
            'name'       => 'Stock Controller',
            'team'       => 'warehouse',
            'department' => 'warehouse',
            'roles'      => [
                'distribution-clerk'
            ]
        ],
        'dist-m'   => [
            'code'       => 'dist-m',
            'name'       => 'Dispatch supervisor',
            'team'       => 'warehouse',
            'department' => 'warehouse',
            'roles'      => [
                'distribution-dispatcher-admin'
            ]
        ],
        'dist-pik' => [
            'code'       => 'dist-pik',
            'team'       => 'warehouse',
            'department' => 'warehouse',
            'name'       => 'Picker',
            'roles'      => [
                'distribution-dispatcher-picker'
            ]
        ],
        'dist-pak' => [
            'code'       => 'dist-pak',
            'team'       => 'warehouse',
            'department' => 'warehouse',
            'name'       => 'Packer',
            'roles'      => [
                'distribution-dispatcher-packer'
            ]
        ],
        'prod-m'   => [
            'code'       => 'prod-m',
            'team'       => 'production',
            'department' => 'production',
            'name'       => 'Production supervisor',
            'roles'      => [
                'guest'
            ]
        ],
        'prod-w'   => [
            'code'       => 'prod-w',
            'team'       => 'production',
            'department' => 'production',
            'name'       => 'Production operative',
            'roles'      => [
                'guest'
            ]
        ],
        'cus-m'    => [
            'code'  => 'cus-m',
            'grade' => 'manager',
            'name'  => 'Customer service supervisor',
            'roles' => [
                'guest'
            ]
        ],
        'cus-c'    => [
            'code'  => 'cus-c',
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
