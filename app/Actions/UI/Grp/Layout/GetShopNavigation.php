<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Feb 2024 07:12:46 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Grp\Layout;

use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetShopNavigation
{
    use AsAction;

    public function handle(Shop $shop, User $user): array
    {
        $navigation = [];

        if ($user->hasPermissionTo("products.$shop->id.view")) {
            $navigation["catalogue"] = [
                "root"  => "grp.org.shops.show.catalogue.",
                "icon"  => ["fal", "fa-cube"],
                "label" => __("catalogue"),
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
                        [
                            "label"   => __("collections"),
                            "tooltip" => __("Collections"),
                            "icon"    => ["fal", "fa-cube"],
                            'root'    => 'grp.org.shops.show.catalogue.collections.',
                            "route"   => [
                                "name"       => "grp.org.shops.show.catalogue.collections.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                    ],
                ],
            ];

            $navigation["billables"] = [
                "root"  => "grp.org.shops.show.billables.",
                "icon"  => ["fal", "fa-ballot"],
                "label" => __("Billables"),
                "route" => [
                    "name"       => 'grp.org.shops.show.billables.dashboard',
                    "parameters" => [$shop->organisation->slug, $shop->slug],
                ],
                "topMenu" => [
                    "subSections" => [
                        [
                            "tooltip" => __("shop"),
                            "icon"    => ["fal", "fa-store-alt"],
                            'root'    => 'grp.org.shops.show.billables.dashboard',
                            "route"   => [
                                "name"       => 'grp.org.shops.show.billables.dashboard',
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("Shipping"),
                            "tooltip" => __("Shipping"),
                            "icon"    => ["fal", "fa-shipping-fast"],
                            'root'    => 'grp.org.shops.show.billables.shipping.',
                            "route"   => [
                                "name"       => "grp.org.shops.show.billables.shipping.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("Charges"),
                            "tooltip" => __("Charges"),
                            "icon"    => ["fal", "fa-charging-station"],
                            'root'    => 'grp.org.shops.show.billables.charges.',
                            "route"   => [
                                "name"       => "grp.org.shops.show.billables.charges.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("Services"),
                            "tooltip" => __("Services"),
                            "icon"    => ["fal", "fa-concierge-bell"],
                            'root'    => 'grp.org.shops.show.billables.services.',
                            "route"   => [
                                "name"       => "grp.org.shops.show.billables.services.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("Insurance"),
                            "tooltip" => __("Insurance"),
                            "icon"    => ["fal", "fa-house-damage"],
                            'root'    => 'grp.org.shops.show.billables.insurances.',
                            "route"   => [
                                "name"       => "grp.org.shops.show.billables.insurances.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],

                    ],
                ],
            ];
        }
        if ($user->hasPermissionTo("products.$shop->id.view")) {
            $navigation["offers"] = [
                "root"  => "grp.org.shops.show.offers.",
                "icon"  => ["fal", "fa-badge-percent"],
                "label" => __("Offers"),
                "route" => [
                    "name"       => 'grp.org.shops.show.offers.dashboard',
                    "parameters" => [$shop->organisation->slug, $shop->slug],
                ],
                "topMenu" => [
                    "subSections" => [
                        [
                            "tooltip" => __("offers dashboard"),
                            "icon"    => ["fal", "fa-chart-network"],
                            'root'    => 'grp.org.shops.show.offers.dashboard',
                            "route"   => [
                                "name"       => 'grp.org.shops.show.offers.dashboard',
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("campaigns"),
                            "tooltip" => __("campaigns"),
                            "icon"    => ["fal", "fa-comment-dollar"],
                            'root'    => 'grp.org.shops.show.offers.campaigns.',
                            "route"   => [
                                "name"       => "grp.org.shops.show.offers.campaigns.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("offers"),
                            "tooltip" => __("offers"),
                            "icon"    => ["fal", "fa-badge-percent"],
                            'root'    => 'grp.org.shops.show.offers.offers.',
                            "route"   => [
                                "name"       => "grp.org.shops.show.offers.offers.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                    ],
                ],
            ];
        }
        if ($user->hasPermissionTo("products.$shop->id.view")) {
            $navigation["marketing"] = [
                "root"  => "grp.org.shops.show.marketing.",
                "icon"  => ["fal", "fa-bullhorn"],
                "label" => __("Marketing"),
                "route" => [
                    "name"       => 'grp.org.shops.show.marketing.dashboard',
                    "parameters" => [$shop->organisation->slug, $shop->slug],
                ],
                "topMenu" => [
                    "subSections" => [
                        [
                            "tooltip" => __("marketing dashboard"),
                            "icon"    => ["fal", "fa-chart-network"],
                            'root'    => 'grp.org.shops.show.marketing.dashboard',
                            "route"   => [
                                "name"       => 'grp.org.shops.show.marketing.dashboard',
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("newsletters"),
                            "tooltip" => __("newsletters"),
                            "icon"    => ["fal", "fa-newspaper"],
                            "route"   => [
                                "name"       => "grp.org.shops.show.marketing.newsletters.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("mailshots"),
                            "tooltip" => __("marketing mailshots"),
                            "icon"    => ["fal", "fa-mail-bulk"],
                            "route"   => [
                                "name"       => "grp.org.shops.show.marketing.mailshots.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("notifications"),
                            "tooltip" => __("notifications"),
                            "icon"    => ["fal", "fa-bell"],
                            "route"   => [
                                "name"       => "grp.org.shops.show.marketing.notifications.index",
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
                "label"   => __("Deals"),
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
            $navigation["ordering"] = [
                "root"  => "grp.org.shops.show.ordering.",
                "scope" => "shops",
                "label" => __("orders"),
                "icon"  => ["fal", "fa-shopping-cart"],
                "route" => [
                    "name"       => 'grp.org.shops.show.ordering.backlog',
                    "parameters" => [$shop->organisation->slug, $shop->slug],
                ],
                "topMenu" => [
                    "subSections" => [
                        [
                            "label"   => __('Backlog'),
                            "tooltip" => __('Pending orders'),
                            "icon"    => ["fal", "fa-tasks-alt"],
                            'root'    => 'grp.org.shops.show.ordering.backlog',
                            "route"   => [
                                "name"       => "grp.org.shops.show.ordering.backlog",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        [
                            "label"   => __("orders"),
                            "tooltip" => __("Orders"),
                            "icon"    => ["fal", "fa-shopping-cart"],
                            'root'    => 'grp.org.shops.show.ordering.orders.',
                            "route"   => [
                                "name"       => "grp.org.shops.show.ordering.orders.index",
                                "parameters" => [$shop->organisation->slug, $shop->slug],
                            ],
                        ],
                        /*
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
                        */
                    ],
                ],
            ];
        }

        return $navigation;
    }
}
