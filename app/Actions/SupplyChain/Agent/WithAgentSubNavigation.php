<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 12-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\SupplyChain\Agent;

use App\Models\SupplyChain\Agent;

trait WithAgentSubNavigation
{
    protected function getAgentNavigation(Agent $parent): array
    {
        return [
            [
                "isAnchor" => true,
                "label"    => __($parent->slug),

                "href"     => [
                    "name"       => "grp.supply-chain.agents.show",
                    "parameters" => [$parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-people-arrows"],
                    "tooltip" => __("Agent"),
                ],
            ],
            [
                "number"   => $parent->stats->number_suppliers,
                "label"    => __("Suppliers"),
                "href"     => [
                    "name"       => "grp.supply-chain.agents.show.suppliers.index",
                    "parameters" => [$parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-person-dolly"],
                    "tooltip" => __("Suppliers"),
                ],
            ],
            [
                "number"   => $parent->stats->number_supplier_products,
                "label"    => __("Products"),
                "href"     => [
                    "name"       => "grp.supply-chain.agents.show.supplier_products.index",
                    "parameters" => [$parent],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-box-usd"],
                    "tooltip" => __("Products"),
                ],
            ],
            // [
            //     "number"   => $parent->stats->number_purchase_orders,
            //     "label"    => __("Purchase Orders"),
            //     "href"     => [
            //         "name"       => "grp.org.procurement.org_agents.show.purchase-orders.index",
            //         "parameters" => [$parent->organisation->slug, $parent->slug],
            //     ],
            //     "leftIcon" => [
            //         "icon"    => ["fal", "fa-clipboard"],
            //         "tooltip" => __("Purchase Orders"),
            //     ],
            // ],
            // [
            //     "number"   => $parent->agent->organisation->inventoryStats->number_org_stocks,
            //     "label"    => __("Org Stocks"),
            //     "href"     => [
            //         "name"       => "grp.org.procurement.org_agents.show.org-stocks.index",
            //         "parameters" => [$parent->organisation->slug, $parent->slug],
            //     ],
            //     "leftIcon" => [
            //         "icon"    => ["fal", "fa-box"],
            //         "tooltip" => __("Org Stocks"),
            //     ],
            // ],
            // [
            //     "number"   => $parent->agent->organisation->inventoryStats->number_deliveries,
            //     "label"    => __("Stock Deliveries"),
            //     "href"     => [
            //         "name"       => "grp.org.procurement.org_agents.show.stock-deliveries.index",
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
