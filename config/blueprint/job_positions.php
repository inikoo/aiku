<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 03 Feb 2022 18:34:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

use App\Enums\Market\Shop\ShopTypeEnum;
use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;

return [


    'positions' => [
        'admin' => [
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
        'hr-m'  => [
            'code'               => 'hr-m',
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
        'hr-c'  => [
            'code'               => 'hr-c',
            'name'               => 'Human resources clerk',
            'department'         => 'admin',
            'roles'              => [
                RolesEnum::HUMAN_RESOURCES_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
                OrganisationTypeEnum::AGENT
            ]
        ],
        'acc-m' => [
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
        'acc-c' => [
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
        'mrk-m' => [
            'code'               => 'mrk-m',
            'name'               => 'Marketing supervisor',
            'roles'              => [
                RolesEnum::MARKETING_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'mrk-c' => [
            'code'               => 'mrk-c',
            'name'               => 'Marketing clerk',
            'roles'              => [
                RolesEnum::MARKETING_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'shk-m' => [
            'code'               => 'web-m',
            'name'               => 'Shopkeeper supervisor',
            'roles'              => [
                RolesEnum::SHOPKEEPER_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'shk-c' => [
            'code'               => 'web-c',
            'name'               => 'Shopkeeper clerk',
            'roles'              => [
                RolesEnum::SHOPKEEPER_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'buy'   => [
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
        'wah-m' => [
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
        'prod-d'   => [
            'code'               => 'prod-d',
            'team'               => 'production',
            'department'         => 'production',
            'name'               => 'Manufacturing dispatcher',
            'roles'              => [
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
            ]
        ],
        'prod-m'   => [
            'code'               => 'prod-m',
            'team'               => 'production',
            'department'         => 'production',
            'name'               => 'Manufacturing floor supervisor',
            'roles'              => [
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
            ]
        ],
        'prod-c'   => [
            'code'               => 'prod-c',
            'team'               => 'production',
            'department'         => 'production',
            'name'               => 'Manufacturing operative',
            'roles'              => [

            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
            ]
        ],
        'cus-m'    => [
            'code'               => 'cus-m',
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
            'name'               => 'Customer service',
            'roles'              => [
                RolesEnum::CUSTOMER_SERVICE_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'seo-m' => [
            'code'               => 'seo-m',
            'name'               => 'Seo supervisor',
            'roles'              => [
                RolesEnum::SEO_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'seo-c' => [
            'code'               => 'seo-c',
            'name'               => 'SEO',
            'roles'              => [
                RolesEnum::SEO_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'ppc-m' => [
            'code'               => 'ppc-m',
            'name'               => 'PPC supervisor',
            'roles'              => [
                RolesEnum::PPC_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'ppc-c' => [
            'code'               => 'ppc-c',
            'name'               => 'PPC',
            'roles'              => [
                RolesEnum::PPC_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'social-m' => [
            'code'               => 'social-m',
            'grade'              => 'manager',
            'name'               => 'Social media supervisor',
            'roles'              => [
                RolesEnum::SOCIAL_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'social-c' => [
            'code'               => 'social-c',
            'grade'              => 'clerk',
            'name'               => 'Social media',
            'roles'              => [
                RolesEnum::SOCIAL_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'saas-m' => [
            'code'               => 'saas-m',
            'grade'              => 'manager',
            'name'               => 'SaaS supervisor',
            'roles'              => [
                RolesEnum::SAAS_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'saas-c' => [
            'code'               => 'saas-c',
            'grade'              => 'clerk',
            'name'               => 'SaaS',
            'roles'              => [
                RolesEnum::SAAS_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'ful-m' => [
            'code'               => 'ful-m',
            'name'               => 'Fulfilment supervisor',
            'department'         => 'fulfilment',
            'roles'              => [
                RolesEnum::FULFILMENT_SHOP_SUPERVISOR,
                RolesEnum::FULFILMENT_WAREHOUSE_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
            ],
            'has_shop_type'=> [
                ShopTypeEnum::FULFILMENT
            ]

        ],
        'ful-c' => [
            'code'               => 'ful-c',
            'name'               => 'Fulfilment shop clerk',
            'department'         => 'fulfilment',
            'roles'              => [
                RolesEnum::FULFILMENT_SHOP_CLERK,
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
            ],
            'extra_conditions'=> [
                'has_shop_type' => 'fulfilment'
            ]

        ],
        'ful-wc' => [
            'code'               => 'ful-wc',
            'name'               => 'Fulfilment warehouse clerk',
            'department'         => 'fulfilment',
            'roles'              => [
                RolesEnum::FULFILMENT_WAREHOUSE_WORKER
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
            ],
            'extra_conditions'=> [
                'has_shop_type' => 'fulfilment'
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
