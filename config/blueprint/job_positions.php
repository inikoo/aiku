<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 03 Feb 2022 18:34:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

use App\Enums\HumanResources\JobPosition\JobPositionScopeEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;

return [


    'positions' => [
        'admin'    => [
            'code'               => 'admin',
            'name'               => 'Administrator',
            'scope'              => JobPositionScopeEnum::ORGANISATION,
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
            'code'       => 'hr-m',
            'name'       => 'Human resources supervisor',
            'scope'      => JobPositionScopeEnum::ORGANISATION,
            'department' => 'admin',

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
            'scope'              => JobPositionScopeEnum::ORGANISATION,
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
        'acc-m'    => [
            'code'               => 'acc-m',
            'department'         => 'admin',
            'scope'              => JobPositionScopeEnum::ORGANISATION,
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
            'scope'              => JobPositionScopeEnum::ORGANISATION,
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
            'name'               => 'Deals supervisor',
            'scope'              => JobPositionScopeEnum::SHOPS,
            'department'         => 'products',
            'roles'              => [
                RolesEnum::MARKETING_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'mrk-c'    => [
            'code'               => 'mrk-c',
            'name'               => 'Deals clerk',
            'scope'              => JobPositionScopeEnum::SHOPS,
            'department'         => 'products',
            'roles'              => [
                RolesEnum::MARKETING_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'shk-m'    => [
            'code'               => 'web-m',
            'name'               => 'Shopkeeper supervisor',
            'department'         => 'products',
            'scope'              => JobPositionScopeEnum::SHOPS,
            'roles'              => [
                RolesEnum::SHOPKEEPER_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'shk-c'    => [
            'code'               => 'web-c',
            'name'               => 'Shopkeeper clerk',
            'scope'              => JobPositionScopeEnum::SHOPS,
            'department'         => 'products',
            'roles'              => [
                RolesEnum::SHOPKEEPER_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'buy'      => [
            'code'               => 'buy',
            'name'               => 'Buyer',
            'scope'              => JobPositionScopeEnum::ORGANISATION,
            'department'         => 'products',
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
            'name'       => 'Warehouse supervisor',
            'scope'      => JobPositionScopeEnum::WAREHOUSES,
            'team'       => 'warehouse',
            'department' => 'warehouse',

            'roles' => [
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
            'scope'              => JobPositionScopeEnum::WAREHOUSES,
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
            'name'               => 'Dispatching supervisor',
            'scope'              => JobPositionScopeEnum::ORGANISATION,
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
            'code'       => 'dist-pik',
            'name'       => 'Picker',
            'scope'      => JobPositionScopeEnum::WAREHOUSES,
            'team'       => 'warehouse',
            'department' => 'warehouse',

            'roles'              => [
                RolesEnum::DISPATCH_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
                OrganisationTypeEnum::AGENT
            ],
        ],
        'dist-pak' => [
            'code'       => 'dist-pak',
            'name'       => 'Packer',
            'scope'      => JobPositionScopeEnum::WAREHOUSES,
            'team'       => 'warehouse',
            'department' => 'warehouse',

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
            'name'               => 'Manufacturing dispatcher',
            'scope'              => JobPositionScopeEnum::PRODUCTIONS,
            'team'               => 'production',
            'department'         => 'production',
            'roles'              => [
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
            ]
        ],
        'prod-m'   => [
            'code'               => 'prod-m',
            'name'               => 'Manufacturing floor supervisor',
            'scope'              => JobPositionScopeEnum::PRODUCTIONS,
            'team'               => 'production',
            'department'         => 'production',
            'roles'              => [
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
            ]
        ],
        'prod-c'   => [
            'code'       => 'prod-c',
            'name'       => 'Manufacturing operative',
            'scope'      => JobPositionScopeEnum::PRODUCTIONS,
            'team'       => 'production',
            'department' => 'production',

            'roles'              => [

            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
            ]
        ],
        'cus-m'    => [
            'code'               => 'cus-m',
            'name'               => 'Customer service supervisor',
            'scope'              => JobPositionScopeEnum::SHOPS,
            'department'         => 'customer-services',
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
            'department'         => 'customer-services',
            'scope'              => JobPositionScopeEnum::SHOPS,
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
            'name'               => 'Seo supervisor',
            'scope'              => JobPositionScopeEnum::ORGANISATION,
            'roles'              => [
                RolesEnum::SEO_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'seo-c'    => [
            'code'  => 'seo-c',
            'name'  => 'SEO',
            'scope' => JobPositionScopeEnum::ORGANISATION,

            'roles'              => [
                RolesEnum::SEO_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'ppc-m'    => [
            'code'  => 'ppc-m',
            'name'  => 'PPC supervisor',
            'scope' => JobPositionScopeEnum::ORGANISATION,

            'roles'              => [
                RolesEnum::PPC_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'ppc-c'    => [
            'code'  => 'ppc-c',
            'name'  => 'PPC',
            'scope' => JobPositionScopeEnum::ORGANISATION,

            'roles'              => [
                RolesEnum::PPC_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'social-m' => [
            'code'  => 'social-m',
            'grade' => 'manager',
            'scope' => JobPositionScopeEnum::ORGANISATION,

            'name'               => 'Social media supervisor',
            'roles'              => [
                RolesEnum::SOCIAL_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'social-c' => [
            'code'  => 'social-c',
            'grade' => 'clerk',
            'name'  => 'Social media',
            'scope' => JobPositionScopeEnum::ORGANISATION,

            'roles'              => [
                RolesEnum::SOCIAL_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'saas-m'   => [
            'code'  => 'saas-m',
            'grade' => 'manager',
            'name'  => 'SaaS supervisor',
            'scope' => JobPositionScopeEnum::ORGANISATION,

            'roles'              => [
                RolesEnum::SAAS_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'saas-c'   => [
            'code'  => 'saas-c',
            'grade' => 'clerk',
            'name'  => 'SaaS',
            'scope' => JobPositionScopeEnum::ORGANISATION,

            'roles'              => [
                RolesEnum::SAAS_CLERK
            ],
            'organisation_types' => [
                OrganisationTypeEnum::DIGITAL_AGENCY,
            ]
        ],
        'ful-m'    => [
            'code'       => 'ful-m',
            'name'       => 'Fulfilment supervisor',
            'scope'      => JobPositionScopeEnum::FULFILMENTS_WAREHOUSES,
            'department' => 'fulfilment',

            'roles'              => [
                RolesEnum::FULFILMENT_SHOP_SUPERVISOR,
                RolesEnum::FULFILMENT_WAREHOUSE_SUPERVISOR
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
            ],
            'has_shop_type'      => [
                ShopTypeEnum::FULFILMENT
            ]

        ],
        'ful-c'    => [
            'code'               => 'ful-c',
            'name'               => 'Fulfilment shop clerk',
            'scope'              => JobPositionScopeEnum::FULFILMENTS,
            'department'         => 'fulfilment',
            'roles'              => [
                RolesEnum::FULFILMENT_SHOP_CLERK,
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
            ],
            'extra_conditions'   => [
                'has_shop_type' => 'fulfilment'
            ]

        ],
        'ful-wc'   => [
            'code'               => 'ful-wc',
            'name'               => 'Fulfilment warehouse clerk',
            'scope'              => JobPositionScopeEnum::WAREHOUSES,
            'department'         => 'fulfilment',
            'roles'              => [
                RolesEnum::FULFILMENT_WAREHOUSE_WORKER
            ],
            'organisation_types' => [
                OrganisationTypeEnum::SHOP,
            ],
            'extra_conditions'   => [
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
