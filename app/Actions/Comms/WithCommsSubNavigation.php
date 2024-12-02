<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms;

use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;

trait WithCommsSubNavigation
{
    protected function getCommsNavigation(Shop|Fulfilment $parent): array
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
