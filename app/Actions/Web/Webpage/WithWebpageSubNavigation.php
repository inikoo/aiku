<?php
/*
 * author Arya Permana - Kirin
 * created on 09-10-2024-15h-50m
 * github: https://github.com/KirinZero0
 * copyright 2024
 */

namespace App\Actions\Web\Webpage;

use App\Models\Catalogue\Shop;
use App\Models\Web\Website;

trait WithWebpageSubNavigation
{
    protected function getWebpageNavigation(Shop|Website $parent): array
    {
        if ($parent instanceof Shop) {
            $shop    = $parent;
            $website = $parent->website;
        } else {
            $website = $parent;
            $shop    = $parent->shop;
        }

        return [
            [
                "route"     => [
                    "name"       => "grp.org.shops.show.web.webpages.show",
                    "parameters" => [
                        $shop->organisation->slug,
                        $shop->slug,
                        $shop->website->slug,
                        $shop->website->storefront->slug,
                    ],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-home"],
                    "tooltip" => __("Homepage"),
                ],
            ],
            [
                "isAnchor" => true,
                "label"    => __("Structure"),

                "route"     => [
                    "name"       => "grp.org.shops.show.web.webpages.tree",
                    "parameters" => [$shop->organisation->slug, $shop->slug, $shop->website->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-code-branch"],
                    "tooltip" => __("Tree view of the webpages"),
                ],
            ],
            [
                "number"   => $website->webStats->number_webpages_type_catalogue,
                "label"    => __("Catalogue"),
                "route"     => [
                    "name"       => "grp.org.shops.show.web.webpages.index.type.catalogue",
                    "parameters" => [$shop->organisation->slug, $shop->slug, $shop->website->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-books"],
                    "tooltip" => __("Catalogue webpages"),
                ],
            ],
            [
                "number"   => $website->webStats->number_webpages_type_content,
                "label"    => __("Content"),
                "route"     => [
                    "name"       => "grp.org.shops.show.web.webpages.index.type.content",
                    "parameters" => [$shop->organisation->slug, $shop->slug, $shop->website->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-columns"],
                    "tooltip" => __("Content pages"),
                ],
            ],
            [
                "number"   => $website->webStats->number_webpages_type_info,
                "label"    => __("Info"),
                "route"     => [
                    "name"       => "grp.org.shops.show.web.webpages.index.type.info",
                    "parameters" => [$shop->organisation->slug, $shop->slug, $shop->website->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-info-circle"],
                    "tooltip" => __("Info pages"),
                ],
            ],
            [
                "number"   => $website->webStats->number_webpages_type_operations,
                "label"    => __("Operations"),
                "route"     => [
                    "name"       => "grp.org.shops.show.web.webpages.index.type.operations",
                    "parameters" => [$shop->organisation->slug, $shop->slug, $shop->website->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-sign-in-alt"],
                    "tooltip" => __("Operations webpages"),
                ],
            ],

            [
                "number"   => $website->webStats->number_webpages_type_blog,
                "label"    => __("Blog"),
                "route"     => [
                    "name"       => "grp.org.shops.show.web.webpages.index.type.blog",
                    "parameters" => [$shop->organisation->slug, $shop->slug, $shop->website->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-newspaper"],
                    "tooltip" => __("Operations blog"),
                ],
            ],

            [
                "number"   => $website->webStats->number_webpages,
                "align"    => "right",
                "label"    => __("All"),
                "route"     => [
                    "name"       => "grp.org.shops.show.web.webpages.index",
                    "parameters" => [$shop->organisation->slug, $shop->slug, $shop->website->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-stream"],
                    "tooltip" => __("All Webpages"),
                ],
            ],
        ];
    }
}
