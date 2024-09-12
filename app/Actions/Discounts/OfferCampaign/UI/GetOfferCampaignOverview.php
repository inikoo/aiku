<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 11:53:47 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Discounts\OfferCampaign\UI;

use App\Http\Resources\Catalogue\OfferCampaignResource;
use App\Models\Discounts\OfferCampaign;
use Lorisleiva\Actions\Concerns\AsObject;

class GetOfferCampaignOverview
{
    use AsObject;

    public function handle(OfferCampaign $offerCampaign): array
    {
        return [
            'offerCampaign' => OfferCampaignResource::make($offerCampaign),
            // 'stats'   => $offerCampaign->stats,
            'stats'   => [
                [
                    "label" => "Department",
                    "icon"  => "fal fa-folder-tree",
                    "value" => 16,
                    "meta"  => [
                        "value" => "+4",
                        "label" => "from last month"
                    ]
                ],
                [
                    "label" => "Families",
                    "icon"  => "fal fa-folder",
                    "value" => 2350,
                    "meta"  => [
                        "value" => "+4",
                        "label" => "from last month"
                    ]
                ],
                [
                    "label" => "Products",
                    "icon"  => "fal fa-cube",
                    "value" => 23102,
                    "meta"  => [
                        "value" => "+4",
                        "label" => "from last month"
                    ]
                ],
                [
                    "label" => "Collections",
                    "icon"  => "fal fa-cube",
                    "value" => 0,
                    "meta"  => [
                        "value" => "+4",
                        "label" => "from last month"
                    ]
                ]
            ]
        ];
    }
}
