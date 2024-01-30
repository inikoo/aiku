<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 Jan 2024 01:18:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Layout;

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
                "scope" => "shops",
                "icon"  => ["fal", "fa-store-alt"],
                "label" => __("Products"),
                "route" => [
                    "name"       => "grp.org.shops.show",
                    "parameters" => [$shop->organisation->slug, $shop->slug],
                ],
                "topMenu" => [
                    "subSections" => [
                        [
                            "tooltip" => __("shop"),
                            "icon"    => ["fal", "fa-store-alt"],
                            "route"   => [
                                "name"       => "grp.org.shops.show",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("departments"),
                            "tooltip" => __("Departments"),
                            "icon"    => ["fal", "fa-folder-tree"],
                            "route"   => [
                                "name"       => "grp.org.shops.show.departments.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("families"),
                            "tooltip" => __("Families"),
                            "icon"    => ["fal", "fa-folder"],
                            "route"   => [
                                "name"       => "grp.org.shops.show.families.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("products"),
                            "tooltip" => __("Products"),
                            "icon"    => ["fal", "fa-cube"],
                            "route"   => [
                                "name"       => "grp.org.shops.show.products.index",
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
                    "scope" => "websites",
                    "icon"  => ["fal", "fa-globe"],
                    "label" => __("Website"),
                    "route" => [
                        "name"       => "grp.org.shops.show.websites.show",
                        "parameters" => [$shop->organisation->slug, $shop->slug, $shop->website->slug],
                    ],

                    "topMenu" => [
                        "subSections" => [


                            [
                                "label"   => __("Website"),
                                "tooltip" => __("website"),
                                "icon"    => ["fal", "fa-globe"],
                                "route"   => [
                                    "name"       => "grp.org.shops.show.websites.show",
                                    "parameters" => [$shop->organisation->slug, $shop->slug, $shop->website->slug],
                                ],
                            ],
                            [
                                "label"   => __("webpages"),
                                "tooltip" => __("Webpages"),
                                "icon"    => ["fal", "fa-browser"],
                                "route"   => [
                                    "name"       => "grp.org.shops.show.websites.show.webpages.index",
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
                    "route" => [
                        "name"       => "grp.org.shops.show.websites.index",
                        "parameters" => [$shop->organisation->slug, $shop->slug],
                    ],

                    "topMenu" => []
                ];
            }



        }

        if ($user->hasPermissionTo("marketing.view")) {
            $navigation["marketing"] = [
                "scope"   => "shops",
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
                "scope" => "shops",
                "label" => __("Customers"),
                "icon"  => ["fal", "fa-user"],

                "route" => [
                    "name"       => "grp.org.shops.show.customers.index",
                    "parameters" => [$shop->organisation->slug, $shop->slug],
                ],

                "topMenu" => [
                    "subSections" => [
                        [
                            "tooltip" => __("Dashboard"),
                            "icon"    => ["fal", "fa-chart-network"],
                            "route"   => [
                                "name"       => "grp.crm.dashboard",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("customers"),
                            "tooltip" => __("Customers"),
                            "icon"    => ["fal", "fa-user"],
                            "route"   => [
                                "name"       => "grp.crm.customers.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("prospects"),
                            "tooltip" => __("Prospects"),
                            "icon"    => ["fal", "fa-user-plus"],
                            "route"   => [
                                "name"       => "grp.crm.prospects.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                    ],
                ],
            ];
        }

        if ($user->hasPermissionTo("oms.$shop->id.view")) {
            $navigation["oms"] = [
                "scope" => "shops",
                "label" => __("Orders"),
                "icon"  => ["fal", "fa-shopping-cart"],
                "route" => [
                    "name"       => "grp.oms.dashboard",
                    "parameters" => [$shop->organisation->slug, $shop->slug],
                ],
                "topMenu" => [
                    "subSections" => [
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
