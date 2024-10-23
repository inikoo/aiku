<?php
/*
 * author Arya Permana - Kirin
 * created on 23-10-2024-13h-18m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Procurement\OrgAgent;

use App\Models\Catalogue\Shop;
use App\Models\Procurement\OrgAgent;
use App\Models\Web\Website;

trait WithOrgAgentSubNavigation
{
    protected function getOrgAgentNavigation(OrgAgent $parent): array
    {
        return [
            [
                "isAnchor" => true,
                "label"    => __($parent->slug),

                "href"     => [
                    "name"       => "grp.org.procurement.org_agents.show",
                    "parameters" => [$parent->organisation->slug, $parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-people-arrows"],
                    "tooltip" => __("Org Agent"),
                ],
            ],
            [
                "number"   => $parent->stats->number_org_suppliers,
                "label"    => __("Suppliers"),
                "href"     => [
                    "name"       => "grp.org.procurement.org_agents.show.suppliers.index",
                    "parameters" => [$parent->organisation->slug, $parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-person-dolly"],
                    "tooltip" => __("Suppliers"),
                ],
            ],
            [
                "number"   => $parent->stats->number_purchase_orders,
                "label"    => __("Purchase Orders"),
                "href"     => [
                    "name"       => "grp.org.procurement.org_agents.show.purchase-orders.index",
                    "parameters" => [$parent->organisation->slug, $parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-clipboard"],
                    "tooltip" => __("Purchase Orders"),
                ],
            ],
            
        ];
    }
}
