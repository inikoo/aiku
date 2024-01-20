<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 03 Feb 2022 18:34:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

return [


    'positions' => [

        'admin'    => [
            'code'               => 'admin',
            'name'               => 'Administrator',
            'department'         => 'admin',
            'roles'              => [
                'super-admin'
            ],
            'organisation_types' => [
                'shop',
                'agent'
            ]
        ],
        'hr-m'     => [
            'code'               => 'hr-m',
            'grade'              => 'manager',
            'department'         => 'admin',
            'name'               => 'Human resources supervisor',
            'roles'              => [
                'human-resources-supervisor'
            ],
            'organisation_types' => [
                'shop',
                'agent'
            ]
        ],
        'hr-c'     => [
            'code'               => 'hr-c',
            'name'               => 'Human resources clerk',
            'department'         => 'admin',
            'grade'              => 'clerk',
            'roles'              => [
                'human-resources'
            ],
            'organisation_types' => [
                'shop',
                'agent'
            ]
        ],
        'acc-m'    => [
            'code'               => 'acc-m',
            'department'         => 'admin',
            'name'               => 'Accounting manager',
            'roles'              => [
                'accounting-supervisor'
            ],
            'organisation_types' => [
                'shop',
                'agent'
            ]
        ],
        'acc-c'    => [
            'code'               => 'acc-c',
            'department'         => 'admin',
            'name'               => 'Accounts',
            'roles'              => [
                'accounting'
            ],
            'organisation_types' => [
                'shop',
                'agent'
            ],
        ],
        'mrk-m'    => [
            'code'               => 'mrk-m',
            'grade'              => 'manager',
            'name'               => 'Marketing supervisor',
            'roles'              => [
                'guest'
            ],
            'organisation_types' => [
                'shop'
            ]
        ],
        'mrk-c'    => [
            'code'               => 'mrk-c',
            'grade'              => 'clerk',
            'name'               => 'Marketing clerk',
            'roles'              => [
                'guest'
            ],
            'organisation_types' => [
                'shop'
            ]
        ],
        'web-m'    => [
            'code'               => 'web-m',
            'grade'              => 'manager',
            'name'               => 'Webmaster supervisor',
            'roles'              => [
                'guest'
            ],
            'organisation_types' => [
                'shop'
            ]
        ],
        'web-c'    => [
            'code'               => 'web-c',
            'grade'              => 'clerk',
            'name'               => 'Webmaster clerk',
            'roles'              => [
                'guest'
            ],
            'organisation_types' => [
                'shop'
            ]
        ],
        'buy'      => [
            'code'               => 'buy',
            'name'               => 'Buyer',
            'roles'              => [
                'guest'
            ],
            'organisation_types' => [
                'shop',
                'agent'
            ],
        ],
        'wah-m'    => [
            'code'       => 'wah-m',
            'team'       => 'warehouse',
            'department' => 'procurement',
            'name'       => 'Warehouse supervisor',
            'roles'      => [
                'distribution-admin'
            ],

            'organisation_types' => [
                'shop',
                'agent'
            ],
        ],
        'wah-sk'   => [
            'code'               => 'wah-sk',
            'team'               => 'warehouse',
            'department'         => 'warehouse',
            'name'               => 'Warehouse stock keeper',
            'roles'              => [
                'guest'
            ],
            'organisation_types' => [
                'shop',
                'agent'
            ],
        ],
        'wah-sc'   => [
            'code'               => 'wah-sc',
            'name'               => 'Stock Controller',
            'team'               => 'warehouse',
            'department'         => 'warehouse',
            'roles'              => [
                'distribution-clerk'
            ],
            'organisation_types' => [
                'shop',
                'agent'
            ],
        ],
        'dist-m'   => [
            'code'               => 'dist-m',
            'name'               => 'Dispatch supervisor',
            'team'               => 'warehouse',
            'department'         => 'warehouse',
            'roles'              => [
                'distribution-dispatcher-admin'
            ],
            'organisation_types' => [
                'shop',
                'agent'
            ],
        ],
        'dist-pik' => [
            'code'               => 'dist-pik',
            'team'               => 'warehouse',
            'department'         => 'warehouse',
            'name'               => 'Picker',
            'roles'              => [
                'distribution-dispatcher-picker'
            ],
            'organisation_types' => [
                'shop',
                'agent'
            ],
        ],
        'dist-pak' => [
            'code'               => 'dist-pak',
            'team'               => 'warehouse',
            'department'         => 'warehouse',
            'name'               => 'Packer',
            'roles'              => [
                'distribution-dispatcher-packer'
            ],
            'organisation_types' => [
                'shop',
                'agent'
            ],
        ],
        'prod-m'   => [
            'code'               => 'prod-m',
            'team'               => 'production',
            'department'         => 'production',
            'name'               => 'Production supervisor',
            'roles'              => [
                'guest'
            ],
            'organisation_types' => [
                'shop'
            ]
        ],
        'prod-w'   => [
            'code'               => 'prod-w',
            'team'               => 'production',
            'department'         => 'production',
            'name'               => 'Production operative',
            'roles'              => [
                'guest'
            ],
            'organisation_types' => [
                'shop'
            ]
        ],
        'cus-m'    => [
            'code'               => 'cus-m',
            'grade'              => 'manager',
            'name'               => 'Customer service supervisor',
            'roles'              => [
                'guest'
            ],
            'organisation_types' => [
                'shop'
            ]
        ],
        'cus-c'    => [
            'code'               => 'cus-c',
            'grade'              => 'clerk',
            'name'               => 'Customer service',
            'roles'              => [
                'guest'
            ],
            'organisation_types' => [
                'shop'
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
