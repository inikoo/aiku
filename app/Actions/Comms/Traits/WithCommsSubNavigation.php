<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 19 Dec 2024 18:14:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Traits;

use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;

trait WithCommsSubNavigation
{
    protected function getCommsNavigation(Organisation $organisation, Shop|Fulfilment $parent): array
    {
        if ($parent instanceof Shop) {
            $shop    = $parent;
        } else {
            $shop    = $parent->shop;
        }

        return [

            [
                "isAnchor" => true,
                "label"    => __("Comms Dashboard"),

                "route"     => [
                    "name"       => "grp.org.shops.show.comms.dashboard",
                    "parameters" => [$shop->organisation->slug, $shop->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-chart-network"],
                    "tooltip" => __("Tree view of the webpages"),
                ],
            ],
            [
                "label"    => __("Post Rooms"),
                "route"     => [
                    "name"       => "grp.org.shops.show.comms.post-rooms.index",
                    "parameters" => [$organisation->slug, $shop->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-inbox-out"],
                    "tooltip" => __("Post Rooms"),
                ],
            ],
            [
                "label"    => __("Outboxes"),
                "route"     => [
                    "name"       => "grp.org.shops.show.comms.outboxes.index",
                    "parameters" => [$shop->organisation->slug, $shop->slug],
                ],
                "leftIcon" => [
                    "icon"    => ["fal", "fa-inbox-out"],
                    "tooltip" => __("Outboxes"),
                ],
            ],

        ];
    }
}
