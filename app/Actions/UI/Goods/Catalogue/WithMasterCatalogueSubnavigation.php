<?php
/*
 * author Arya Permana - Kirin
 * created on 15-10-2024-10h-36m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\UI\Goods\Catalogue;

use App\Models\Catalogue\MasterShop;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;

trait WithMasterCatalogueSubnavigation
{
    protected function getMasterCatalogueSubnavigation(): array
    {
        return [
            [
                'href'     => [
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

                'href'     => [
                    'name'       => 'grp.goods.catalogue.products.index',
                    'parameters' => []
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-cube'],
                    'tooltip' => __('Master Products')
                ]
            ]
        ];
    }

    protected function getMasterShopNavigation(MasterShop $masterShop): array
    {

        return [
            [
                'href'     => [
                    'name'       => '',
                    'parameters' => []
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-home'],
                    'tooltip' => __('Homepage')
                ]
            ],
            [
                'isAnchor' => true,
                'label'    => __('Structure'),

                'href'     => [
                    'name'       => '',
                    'parameters' => []
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-code-branch'],
                    'tooltip' => __('Master Shop')
                ]



            ],
            [
                'number'   => 0,
                'label'    => __('Departments'),
                'href'     => [
                    'name'       => '',
                    'parameters' => []
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('Master Departments')
                ]
            ],
            [
                'number'   => 0,
                'label'    => __('Sub Departments'),
                'href'     => [
                    'name'       => '',
                    'parameters' => []
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('Master Sub Departments')
                ]
            ],
            [
                'number'   => 0,
                'label'    => __('Families'),
                'href'     => [
                    'name'       => '',
                    'parameters' => []
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
                'href'     => [
                    'name'       => '',
                    'parameters' => []
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('Master Products')
                ]
            ],

        ];
    }

}
