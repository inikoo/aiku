<?php
/*
 * author Arya Permana - Kirin
 * created on 09-10-2024-15h-50m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Web\Webpage;

use App\Models\Web\Website;

trait WithWebpageSubNavigation
{
    protected function getWebpageNavigation(Website $website): array
    {
        $shop=$website->shop;

        return [
            [
                'href'     => [
                    'name'       => 'grp.org.shops.show.web.webpages.show',
                    'parameters' => [$website->organisation->slug, $shop->slug, $website->slug,$website->storefront->slug]
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
                    'name'       => 'grp.org.shops.show.web.webpages.tree',
                    'parameters' => [$website->organisation->slug, $shop->slug, $website->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-code-branch'],
                    'tooltip' => __('Tree view of the webpages')
                ]



            ],
            [
                'number'   => $website->webStats->number_webpages_type_catalogue,
                'label'    => __('Catalogue'),
                'href'     => [
                    'name'       => 'grp.org.shops.show.web.webpages.index.type.catalogue',
                    'parameters' => [$website->organisation->slug, $shop->slug, $website->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('Catalogue webpages')
                ]
            ],
            [
                'number'   => $website->webStats->number_webpages_type_content,
                'label'    => __('Content'),
                'href'     => [
                    'name'       => 'grp.org.shops.show.web.webpages.index.type.content',
                    'parameters' => [$website->organisation->slug, $shop->slug, $website->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('Content pages')
                ]
            ],
            [
                'number'   => $website->webStats->number_webpages_type_info,
                'label'    => __('Info'),
                'href'     => [
                    'name'       => 'grp.org.shops.show.web.webpages.index.type.info',
                    'parameters' => [$website->organisation->slug, $shop->slug, $website->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('Info pages')
                ]
            ],
            [
                'number'   => $website->webStats->number_webpages_type_operations,
                'label'    => __('Operations'),
                'href'     => [
                    'name'       => 'grp.org.shops.show.web.webpages.index.type.operations',
                    'parameters' => [$website->organisation->slug, $shop->slug, $website->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('Operations webpages')
                ]
            ],

            [
                'number'   => $website->webStats->number_webpages_type_blog,
                'label'    => __('Blog'),
                'href'     => [
                    'name'       => 'grp.org.shops.show.web.webpages.index.type.blog',
                    'parameters' => [$website->organisation->slug, $shop->slug, $website->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('Operations blog')
                ]
            ],

            [
                'number'   => $website->webStats->number_webpages,
                'align'    => 'right',
                'label'    => __('All'),
                'href'     => [
                    'name'       => 'grp.org.shops.show.web.webpages.index',
                    'parameters' => [$website->organisation->slug, $shop->slug, $website->slug]
                ],
                'leftIcon' => [
                    'icon'    => ['fal', 'fa-stream'],
                    'tooltip' => __('All Webpages')
                ]
            ],

        ];
    }

}
