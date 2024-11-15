<?php
/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 15-11-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Actions\Discounts\Offer\Search;

use App\Models\Discounts\Offer;
use Lorisleiva\Actions\Concerns\AsAction;

class OfferRecordSearch
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
                'haystack_tier_1'   => $offer->code,
                'haystack_tier_2'   => $offer->name,
                'result'            => [
                    'route'         => [
                        'name'          => 'grp.org.shops.show.catalogue.departments.show',
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
                    'meta'      => [
                    [
                        'label'   => $offer->state,
                        'tooltip' => __('State'),
                    ],
                    [
                        'type'      => 'date',
                        'label'     => $offer->created_at,
                        'tooltip'   => __('Created at')
                    ],
                    [
                        'type'       => 'currency',
                        'label'      => __('Price') . ': ',
                        'code'       => $offer->currency->code,
                        'amount'     => $offer->price,
                        'tooltip'    => __('Price')
                    ],
                    [
                        'type'           => 'number',
                        'label'          => __('Quantity') . ': ',
                        'number'         => $offer->available_quantity,
                        'afterLabel'     => __('pcs'),
                        'tooltip'        => __('Quantity')
                    ],
                ],
                ]
            ]
        );
    }

}
