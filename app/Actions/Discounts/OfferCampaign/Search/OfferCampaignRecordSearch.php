<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 15-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Discounts\OfferCampaign\Search;

use App\Models\Discounts\Offer;
use Lorisleiva\Actions\Concerns\AsAction;

class OfferCampaignRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Offer $offer): void
    {
        if ($offer->trashed()) {
            $offer->universalSearch()->delete();

            return;
        }

        $offer->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $offer->group_id,
                'organisation_id'   => $offer->organisation_id,
                'organisation_slug' => $offer->organisation->slug,
                'shop_id'           => $offer->shop_id,
                'shop_slug'         => $offer->shop->slug,
                'sections'          => ['discount'],
                'haystack_tier_1'   => trim($offer->code . ' ' . $offer->name),
                'result'            => [
                    'route'         => [
                        'name'          => 'grp.org.shops.show.discounts.offers.show',
                            'parameters'    => [
                                $offer->organisation->slug,
                                $offer->shop->slug,
                                $offer->slug,
                            ]
                    ],
                    'code' => [
                        'label' => $offer->code,
                    ],
                    'description' => [
                        'label' => $offer->name,
                    ],
                    'icon' => [
                        'icon' => 'fal fa-badge-percent',
                    ],
                ]
            ]
        );
    }

}
