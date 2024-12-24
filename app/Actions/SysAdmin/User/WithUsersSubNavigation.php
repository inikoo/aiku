<?php

/*
 * author Arya Permana - Kirin
 * created on 24-12-2024-14h-17m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\SysAdmin\User;

use App\Models\SysAdmin\Group;
use Lorisleiva\Actions\ActionRequest;

trait WithUsersSubNavigation
{
    protected function getUsersNavigation(Group $group, ActionRequest $request): array
    {
        return [
            [
                "number"   => $group->sysadminStats->number_users_status_active,
                "label"    => __("Active Users"),
                "route"     => [
                    "name"       => "grp.sysadmin.users.index",
                    "parameters" => [],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-user"],
                    "tooltip" => __("Active Users"),
                ],
            ],
            [
                "number"   => $group->sysadminStats->number_users_status_inactive,
                "label"    => __("Suspended Users"),
                "route"     => [
                    "name"       => "grp.sysadmin.users.suspended.index",
                    "parameters" => [],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-user-slash"],
                    "tooltip" => __("Suspended Users"),
                ],
            ],
            [
                "number"   => $group->sysadminStats->number_user_requests,
                "label"    => __("User Requests"),
                "route"     => [
                    "name"       => "grp.sysadmin.users.request.index",
                    "parameters" => [],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-road"],
                    "tooltip" => __("User Requests"),
                ],
            ],
            [
                "number"   => $group->sysadminStats->number_users,
                "label"    => __("All Users"),
                "route"     => [
                    "name"       => "grp.sysadmin.users.all.index",
                    "parameters" => [],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-users"],
                    "tooltip" => __("All Users"),
                ],
            ],
            // wait purchase order:
            // [
            //     "number"   => $parent->stats->number_purchase_orders,
            //     "label"    => __("Purchase Orders"),
            //     "route"     => [
            //         "name"       => "grp.org.procurement.org_suppliers.show.purchase_orders.index",
            //         "parameters" => [$parent->organisation->slug, $parent->slug],
            //     ],
            //     "leftIcon" => [
            //         "icon"    => ["fal", "fa-clipboard"],
            //         "tooltip" => __("Purchase Orders"),
            //     ],
            // ],
            // [
            //     "number"   => $parent->stats->number_stock_deliveries,
            //     "label"    => __("Stock Deliveries"),
            //     "route"     => [
            //         "name"       => "grp.org.procurement.org_suppliers.show.stock_deliveries.index",
            //         "parameters" => [$parent->organisation->slug, $parent->slug],
            //     ],
            //     "leftIcon" => [
            //         "icon"    => ["fal", "fa-truck-container"],
            //         "tooltip" => __("Stock Deliveries"),
            //     ],
            // ],

        ];
    }
}
