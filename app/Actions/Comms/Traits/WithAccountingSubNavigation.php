<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 24-02-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Comms\Traits;

use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;

trait WithAccountingSubNavigation
{
    public function getSubNavigation(Fulfilment|Shop $parent): array
    {
        return [
            [
                "isAnchor" => true,
                "label"    => __("Shop Accounting Dashboard"),

                "route"    => [
                    "name"       => "grp.org.fulfilments.show.operations.accounting.dashboard",
                    "parameters" => [$parent->organisation->slug, $parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-chart-network"],
                    "tooltip" => __("Tree view of the webpages"),
                ],
            ],
            [
                "label"    => __("Accounts"),
                "route"    => [
                    "name"       => "grp.org.fulfilments.show.operations.accounting.accounts.index",
                    "parameters" => [$parent->organisation->slug, $parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-inbox-out"],
                    "tooltip" => __("Accounts"),
                ],
            ],
            [
                "label"    => __("Payments"),
                "route"    => [
                    "name"       => "grp.org.fulfilments.show.operations.accounting.payments.index",
                    "parameters" => [$parent->organisation->slug, $parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-inbox-out"],
                    "tooltip" => __("Outboxes"),
                ],
            ],
            [
                "label"    => __("customers balance"),
                "route"    => [
                    "name"       => "grp.org.fulfilments.show.operations.accounting.customer_balances.index",
                    "parameters" => [$parent->organisation->slug, $parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-inbox-out"],
                    "tooltip" => __("customer balance"),
                ],
            ],
        ];
    }
    public function getSubNavigationShop(Shop|Fulfilment $parent): array
    {
        return [
            [
                "isAnchor" => true,
                "label"    => __("Shop Accounting Dashboard"),

                "route"    => [
                    "name"       => "grp.org.shops.show.payments.accounting.dashboard",
                    "parameters" => [$parent->organisation->slug, $parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-chart-network"],
                    "tooltip" => __("Tree view of the webpages"),
                ],
            ],
            // TODO: fix to get payment_account_shop
            // [
            //     "label"    => __("Accounts"),
            //     "route"    => [
            //         "name"       => "grp.org.shops.show.payments.accounting.accounts.index",
            //         "parameters" => [$parent->organisation->slug, $parent->slug],
            //     ],
            //     "leftIcon" => [
            //         "icon"    => ["fal", "fa-inbox-out"],
            //         "tooltip" => __("Accounts"),
            //     ],
            // ],
            [
                "label"    => __("Payments"),
                "route"    => [
                    "name"       => "grp.org.shops.show.payments.accounting.payments.index",
                    "parameters" => [$parent->organisation->slug, $parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-inbox-out"],
                    "tooltip" => __("Outboxes"),
                ],
            ],
            [
                "label"    => __("customers balance"),
                "route"    => [
                    "name"       => "grp.org.shops.show.payments.accounting.customer_balances.index",
                    "parameters" => [$parent->organisation->slug, $parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-inbox-out"],
                    "tooltip" => __("customer balance"),
                ],
            ],
        ];
    }
}
