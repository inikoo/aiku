<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 31 Aug 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent;

use App\Actions\Procurement\OrgAgent\UI\ShowAgentDraftsToSend;
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
                "label"    => __("Drafts to send"),
                "route"    => [
                    "name"       => "grp.org.procurement.org_agents.show.shopping.drafts.index",
                    "parameters" => [$this->organisation->slug, $parent->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-paper-plane"],
                    "tooltip" => __("Drafts to send"),
                ],
                "number"   => ShowAgentDraftsToSend::drafts($parent)->distinct()->count('po.supplier_id'),
            ],
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
            ],
        ];
    }
}
