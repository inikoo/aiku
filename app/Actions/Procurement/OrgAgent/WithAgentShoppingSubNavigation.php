<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 31 Aug 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent;

use App\Actions\Procurement\OrgAgent\UI\ShowAgentDraftsToSend;
use App\Actions\Procurement\OrgAgent\UI\ShowAgentOrderStage;
use App\Models\Procurement\OrgAgent;

trait WithAgentShoppingSubNavigation
{
    protected function getAgentShoppingNavigation(OrgAgent $parent): array
    {
        return [
            [
                "label"    => __("What to order"),
                "route"    => [
                    "name"       => "grp.org.procurement.org_agents.show.shopping.dashboard",
                    "parameters" => [$this->organisation->slug, $parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-shopping-basket"],
                    "tooltip" => __("What to order"),
                ],
                "isAnchor" => true,
            ],
            [
                "label"    => __("Ordering"),
                "route"    => [
                    "name"       => "grp.org.procurement.org_agents.show.shopping.browse.index",
                    "parameters" => [$this->organisation->slug, $parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-store"],
                    "tooltip" => __("Ordering"),
                ],
            ],
            [
                "label"    => __("Drafts to send"),
                "route"    => [
                    "name"       => "grp.org.procurement.org_agents.show.shopping.drafts.index",
                    "parameters" => [$this->organisation->slug, $parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-paper-plane"],
                    "tooltip" => __("Drafts to send"),
                ],
                "number"   => ShowAgentDraftsToSend::drafts($parent)->where('po.organisation_id', $parent->organisation_id)->distinct()->count('po.supplier_id'),
            ],
            ...array_map(fn (string $stage, array $meta) => [
                "label"    => $meta['label'],
                "route"    => [
                    "name"       => "grp.org.procurement.org_agents.show.shopping.stage",
                    "parameters" => [$this->organisation->slug, $parent->slug, $stage],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", $meta['icon']],
                    "tooltip" => $meta['title'],
                ],
                "number"   => $stage === ShowAgentOrderStage::DELIVERED ? null : ShowAgentOrderStage::countOurs($parent, $stage),
            ], array_keys(ShowAgentOrderStage::stages()), ShowAgentOrderStage::stages()),
            [
                "label"    => __("Suppliers"),
                "route"    => [
                    "name"       => "grp.org.procurement.org_agents.show.suppliers.index",
                    "parameters" => [$this->organisation->slug, $parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-person-dolly"],
                    "tooltip" => __("Suppliers"),
                ],
                "number"   => $parent->stats->number_org_suppliers,
                "align"    => "right",
            ],
            [
                "label"    => __("Products"),
                "route"    => [
                    "name"       => "grp.org.procurement.org_agents.show.supplier_products.index",
                    "parameters" => [$this->organisation->slug, $parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-box-usd"],
                    "tooltip" => __("Products"),
                ],
                "number"   => $parent->stats->number_org_supplier_products,
                "align"    => "right",
            ],
        ];
    }
}
