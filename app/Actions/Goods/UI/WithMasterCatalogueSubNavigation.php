<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 29 Dec 2024 03:14:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\UI;

use App\Models\Goods\MasterShop;

trait WithMasterCatalogueSubNavigation
{
    protected function getMasterCatalogueSubNavigation(): array
    {
        return [
            [
                'route'     => [
                    'name'       => 'grp.goods.catalogue.shops.index',
                    'parameters' => []
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-home'],
                    'tooltip' => __('Homepage')
                ]
            ],
            [
                'label'    => __('Products'),

                'route'     => [
                    'name'       => 'grp.goods.catalogue.products.index',
                    'parameters' => []
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-cube'],
                    'tooltip' => __('Master Assets')
                ]
            ]
        ];
    }

    protected function getMasterShopNavigation(MasterShop $masterShop): array
    {

        return [
            [
                'route'     => [
                    'name'       => 'grp.goods.catalogue.shops.index',
                    'parameters' => []
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-home'],
                    'tooltip' => __('Homepage')
                ]
            ],
            [
                'isAnchor' => true,
                'label'    => __($masterShop->name),

                'route'     => [
                    'name'       => 'grp.goods.catalogue.shops.show',
                    'parameters' => [
                        'masterShop' => $masterShop->slug
                    ]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-store-alt'],
                    'tooltip' => __('Master Shop')
                ]



            ],
            [
                'number'   => 0,
                'label'    => __('Departments'),
                'route'     => [
                    'name'       => 'grp.goods.catalogue.shops.show.departments.index',
                    'parameters' => [
                        'masterShop' => $masterShop->slug
                    ]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('Master Departments')
                ]
            ],
            [
                'number'   => 0,
                'label'    => __('Sub Departments'),
                'route'     => [
                    'name'       => 'grp.goods.catalogue.shops.show.sub-departments.index',
                    'parameters' => [
                        'masterShop' => $masterShop->slug
                    ]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('Master Sub Departments')
                ]
            ],
            [
                'number'   => 0,
                'label'    => __('Families'),
                'route'     => [
                    'name'       => 'grp.goods.catalogue.shops.show.families.index',
                    'parameters' => [
                        'masterShop' => $masterShop->slug
                    ]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('Master Families')
                ]
            ],

            [
                'number'   => 0,
                'align'    => 'right',
                'label'    => __('Products'),
                'route'     => [
                    'name'       => 'grp.goods.catalogue.shops.show.products.index',
                    'parameters' => [
                        'masterShop' => $masterShop->slug
                    ]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('Master Products')
                ]
            ],

        ];
    }

}
