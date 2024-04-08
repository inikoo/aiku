<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Feb 2024 07:12:46 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Grp\Layout;

use App\Models\Market\Shop;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetShopNavigation
{
    use AsAction;

    public function handle(Shop $shop, User $user): array
    {
        $navigation = [];

        if ($user->hasPermissionTo("products.$shop->id.view")) {
            $navigation["shop"] = [
                "root"  => "grp.org.shops.show.catalogue.",
                "icon"  => ["fal", "fa-store-alt"],
                "label" => __("Products"),
                "route" => [
                    "name"       => 'grp.org.shops.show.catalogue.dashboard',
                    "parameters" => [$shop->organisation->slug, $shop->slug],
                ],
                "topMenu" => [
                    "subSections" => [
                        [
                            "tooltip" => __("shop"),
                            "icon"    => ["fal", "fa-store-alt"],
                            'root'    => 'grp.org.shops.show.catalogue.dashboard',
                            "route"   => [
                                "name"       => 'grp.org.shops.show.catalogue.dashboard',
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("departments"),
                            "tooltip" => __("Departments"),
                            "icon"    => ["fal", "fa-folder-tree"],
                            'root'    => 'grp.org.shops.show.catalogue.departments.',
                            "route"   => [
                                "name"       => "grp.org.shops.show.catalogue.departments.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("families"),
                            "tooltip" => __("Families"),
                            "icon"    => ["fal", "fa-folder"],
                            'root'    => 'grp.org.shops.show.catalogue.families.',
                            "route"   => [
                                "name"       => "grp.org.shops.show.catalogue.families.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("products"),
                            "tooltip" => __("Products"),
                            "icon"    => ["fal", "fa-cube"],
                            'root'    => 'grp.org.shops.show.catalogue.products.',
                            "route"   => [
                                "name"       => "grp.org.shops.show.catalogue.products.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                    ],
                ],
            ];
        }

        if ($user->hasPermissionTo("web.$shop->id.view")) {

            if($shop->website) {
                $navigation["web"] = [
                    "root"  => "grp.org.shops.show.web.",
                    "icon"  => ["fal", "fa-globe"],
                    "label" => __("Website"),
                    "route" => [
                        "name"       => "grp.org.shops.show.web.websites.show",
                        "parameters" => [$shop->organisation->slug, $shop->slug, $shop->website->slug],
                    ],

                    "topMenu" => [
                        "subSections" => [


                            [
                                "label"   => __("Website"),
                                "tooltip" => __("website"),
                                "icon"    => ["fal", "fa-globe"],
                                "route"   => [
                                    "name"       => "grp.org.shops.show.web.websites.show",
                                    "parameters" => [$shop->organisation->slug, $shop->slug, $shop->website->slug],
                                ],
                            ],
                            [
                                "label"   => __("webpages"),
                                "tooltip" => __("Webpages"),
                                "icon"    => ["fal", "fa-browser"],
                                "route"   => [
                                    "name"       => "grp.org.shops.show.web.websites.show.webpages.index",
                                    "parameters" => [$shop->organisation->slug, $shop->slug, $shop->website->slug],
                                ],
                            ],
                        ],
                    ],
                ];
            } else {
                $navigation["web"] = [
                    "scope" => "websites",
                    "icon"  => ["fal", "fa-globe"],
                    "label" => __("Website"),
                    "root"  => "grp.org.shops.show.web.websites.",
                    "route" => [
                        "name"       => "grp.org.shops.show.web.websites.index",
                        "parameters" => [$shop->organisation->slug, $shop->slug],
                    ],

                    "topMenu" => []
                ];
            }



        }

        if ($user->hasPermissionTo("marketing.view")) {
            $navigation["marketing"] = [
                "root"    => "grp.org.shops.show.marketing.",
                "label"   => __("Marketing"),
                "icon"    => ["fal", "fa-bullhorn"],
                "route"   => "grp.marketing.hub",
                "topMenu" => [
                    "subSections" => [],
                ],
            ];
        }

        if ($user->hasPermissionTo("crm.$shop->id.view")) {
            $navigation["crm"] = [
                "root"  => "grp.org.shops.show.crm.",
                "label" => __("CRM"),
                "icon"  => ["fal", "fa-user"],

                "route" => [
                    "name"       => "grp.org.shops.show.crm.customers.index",
                    "parameters" => [$shop->organisation->slug, $shop->slug],
                ],

                "topMenu" => [
                    "subSections" => [

                        [
                            "label"   => __("customers"),
                            "tooltip" => __("Customers"),
                            "icon"    => ["fal", "fa-user"],
                            "route"   => [
                                "name"       => "grp.org.shops.show.crm.customers.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("prospects"),
                            "tooltip" => __("Prospects"),
                            "icon"    => ["fal", "fa-user-plus"],
                            "route"   => [
                                "name"       => "grp.org.shops.show.crm.prospects.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                    ],
                ],
            ];
        }

        if ($user->hasPermissionTo("orders.$shop->id.view")) {
            $navigation["oms"] = [
                "scope" => "shops",
                "label" => __("Orders"),
                "icon"  => ["fal", "fa-shopping-cart"],
                "route" => [
                    "name"       => 'grp.org.shops.show.orders.orders.index',
                    "parameters" => [$shop->organisation->slug, $shop->slug],
                ],
                "topMenu" => [
                    "subSectionsx" => [
                        [
                            "label"   => "OMS",
                            "tooltip" => "OMS",
                            "icon"    => ["fal", "fa-tasks-alt"],
                            "route"   => [
                                "name"       => "grp.oms.dashboard",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("orders"),
                            "tooltip" => __("Orders"),
                            "icon"    => ["fal", "fa-shopping-cart"],
                            "route"   => [
                                "name"       => "grp.oms.orders.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("delivery notes"),
                            "tooltip" => __("Delivery notes"),
                            "icon"    => ["fal", "fa-truck"],
                            "route"   => [
                                "name"       => "grp.oms.delivery-notes.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("invoices"),
                            "tooltip" => __("Invoices"),
                            "icon"    => ["fal", "fa-file-invoice-dollar"],
                            "route"   => [
                                "name"       => "grp.oms.invoices.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                    ],
                ],
            ];
        }

        return $navigation;
    }
}
