<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 15-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Discounts\OfferCampaign\Search;

use App\Models\Discounts\OfferCampaign;
use Lorisleiva\Actions\Concerns\AsAction;

class OfferCampaignRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(OfferCampaign $offerCampaign): void
    {
        if ($offerCampaign->trashed()) {
            $offerCampaign->universalSearch()->delete();

            return;
        }

        $offerCampaign->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $offerCampaign->group_id,
                'organisation_id'   => $offerCampaign->organisation_id,
                'organisation_slug' => $offerCampaign->organisation->slug,
                'shop_id'           => $offerCampaign->shop_id,
                'shop_slug'         => $offerCampaign->shop->slug,
                'sections'          => ['discount'],
                'haystack_tier_1'   => trim($offerCampaign->code . ' ' . $offerCampaign->name),
                'result'            => [
                    'route'         => [
                        'name'          => 'grp.org.shops.show.discounts.campaigns.show',
                            'parameters'    => [
                                $offerCampaign->organisation->slug,
                                $offerCampaign->shop->slug,
                                $offerCampaign->slug,
                            ]
                    ],
                    'code' => [
                        'label' => $offerCampaign->code,
                    ],
                    'description' => [
                        'label' => $offerCampaign->name,
                    ],
                    'icon' => [
                        'icon' => 'fal fa-comment-dollar',
                    ],
                    'meta' => [
                        [
                            'label'   => $offerCampaign->status,
                            'tooltip' => __('Status')
                        ],
                        [
                            'label'   => __('Number offers') . ': ',
                            'type' => 'number',
                            'number' => $offerCampaign->stats->number_current_offers,
                            'tooltip' => __('Number offers')
                        ]
                    ]
                ]
            ]
        );
    }

}
