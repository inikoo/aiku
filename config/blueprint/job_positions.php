<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 03 Feb 2022 18:34:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;

return [


    'positions' => [

        'admin'    => [
            'code'               => 'admin',
            'name'               => 'Administrator',
            'department'         => 'admin',
            'roles'              => [
               RolesEnum::SUPER_ADMIN
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
                OrganisationTypeEnum::AGENT
            ]
        ],
        'hr-m'     => [
            'code'               => 'hr-m',
            'grade'              => 'manager',
            'department'         => 'admin',
            'name'               => 'Human resources supervisor',
            'roles'              => [
              RolesEnum::HUMAN_RESOURCES_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
                OrganisationTypeEnum::AGENT
            ]
        ],
        'hr-c'     => [
            'code'               => 'hr-c',
            'name'               => 'Human resources clerk',
            'department'         => 'admin',
            'grade'              => 'clerk',
            'roles'              => [
               RolesEnum::HUMAN_RESOURCES_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
                OrganisationTypeEnum::AGENT
            ]
        ],
        'acc-m'    => [
            'code'               => 'acc-m',
            'department'         => 'admin',
            'name'               => 'Accounting manager',
            'roles'              => [
               RolesEnum::ACCOUNTING_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
                OrganisationTypeEnum::AGENT
            ]
        ],
        'acc-c'    => [
            'code'               => 'acc-c',
            'department'         => 'admin',
            'name'               => 'Accounts',
            'roles'              => [
              RolesEnum::ACCOUNTING_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
                OrganisationTypeEnum::AGENT
            ],
        ],
        'mrk-m'    => [
            'code'               => 'mrk-m',
            'grade'              => 'manager',
            'name'               => 'Marketing supervisor',
            'roles'              => [

            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'mrk-c'    => [
            'code'               => 'mrk-c',
            'grade'              => 'clerk',
            'name'               => 'Marketing clerk',
            'roles'              => [

            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'web-m'    => [
            'code'               => 'web-m',
            'grade'              => 'manager',
            'name'               => 'Webmaster supervisor',
            'roles'              => [

            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'web-c'    => [
            'code'               => 'web-c',
            'grade'              => 'clerk',
            'name'               => 'Webmaster clerk',
            'roles'              => [

            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'buy'      => [
            'code'               => 'buy',
            'name'               => 'Buyer',
            'roles'              => [
                RolesEnum::PROCUREMENT_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::AGENT
            ],
        ],
        'wah-m'    => [
            'code'       => 'wah-m',
            'team'       => 'warehouse',
            'department' => 'procurement',
            'name'       => 'Warehouse supervisor',
            'roles'      => [
              RolesEnum::WAREHOUSE_ADMIN
            ],

            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::AGENT
            ],
        ],

        'wah-sc'   => [
            'code'               => 'wah-sc',
            'name'               => 'Stock Controller',
            'team'               => 'warehouse',
            'department'         => 'warehouse',
            'roles'              => [
              RolesEnum::STOCK_CONTROLLER
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::AGENT
            ],
        ],
        'dist-m'   => [
            'code'               => 'dist-m',
            'name'               => 'Dispatch supervisor',
            'team'               => 'warehouse',
            'department'         => 'warehouse',
            'roles'              => [
                RolesEnum::DISPATCH_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::AGENT
            ],
        ],
        'dist-pik' => [
            'code'               => 'dist-pik',
            'team'               => 'warehouse',
            'department'         => 'warehouse',
            'name'               => 'Picker',
            'roles'              => [
                RolesEnum::DISPATCH_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::AGENT
            ],
        ],
        'dist-pak' => [
            'code'               => 'dist-pak',
            'team'               => 'warehouse',
            'department'         => 'warehouse',
            'name'               => 'Packer',
            'roles'              => [
                RolesEnum::DISPATCH_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::AGENT
            ],
        ],
        'prod-m'   => [
            'code'               => 'prod-m',
            'team'               => 'production',
            'department'         => 'production',
            'name'               => 'Production supervisor',
            'roles'              => [
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
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
                OrganisationTypeEnum::SHOP,
            ]
        ],
        'cus-m'    => [
            'code'               => 'cus-m',
            'grade'              => 'manager',
            'name'               => 'Customer service supervisor',
            'roles'              => [
               RolesEnum::CUSTOMER_SERVICE_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'cus-c'    => [
            'code'               => 'cus-c',
            'grade'              => 'clerk',
            'name'               => 'Customer service',
            'roles'              => [
                RolesEnum::CUSTOMER_SERVICE_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],

        'seo-m'    => [
            'code'               => 'seo-m',
            'grade'              => 'manager',
            'name'               => 'Seo supervisor',
            'roles'              => [
                'seo-supervisor'
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'seo-c'    => [
            'code'               => 'seo-c',
            'grade'              => 'clerk',
            'name'               => 'SEO',
            'roles'              => [
                'seo'
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],

        'ppc-m'    => [
            'code'               => 'ppc-m',
            'grade'              => 'manager',
            'name'               => 'PPC supervisor',
            'roles'              => [
                'ppc-supervisor'
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'ppc-c'    => [
            'code'               => 'ppc-c',
            'grade'              => 'clerk',
            'name'               => 'PPC',
            'roles'              => [
                'ppc'
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],

        'social-m'    => [
            'code'               => 'social-m',
            'grade'              => 'manager',
            'name'               => 'Social media supervisor',
            'roles'              => [
                'social-supervisor'
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'social-c'    => [
            'code'               => 'social-c',
            'grade'              => 'clerk',
            'name'               => 'Social media',
            'roles'              => [
                'social'
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],

        'saas-m'    => [
            'code'               => 'saas-m',
            'grade'              => 'manager',
            'name'               => 'SaaS supervisor',
            'roles'              => [
                'saas-supervisor'
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'saas-c'    => [
            'code'               => 'saas-w',
            'grade'              => 'clerk',
            'name'               => 'SaaS',
            'roles'              => [
                'saas'
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
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
