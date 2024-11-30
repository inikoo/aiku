<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 27 Sept 2024 11:46:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Portfolio\Search;

use App\Models\Dropshipping\Portfolio;
use Lorisleiva\Actions\Concerns\AsAction;

class PortfolioRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Portfolio $portfolio): void
    {

        $portfolio->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $portfolio->group_id,
                'organisation_id'   => $portfolio->organisation_id,
                'organisation_slug' => $portfolio->organisation->slug,
                'shop_id'           => $portfolio->shop_id,
                'shop_slug'         => $portfolio->shop->slug,
                'customer_id'       => $portfolio->customer_id,
                'customer_slug'     => $portfolio->customer->slug,
                'sections'          => ['crm'],
                'haystack_tier_1'   => trim($portfolio->product->name),
                'result' => [
                    'route'         => [
                        'name'          => 'grp.org.shops.show.crm.customers.show.portfolios.index',
                        'parameters'    => [
                            $portfolio->organisation->slug,
                            $portfolio->shop->slug,
                            $portfolio->customer->slug
                        ]
                    ],
                    'description' => [
                        'label' => $portfolio->product->name,
                    ],
                    'code'        => [
                        'code' => $portfolio->product->code
                    ],
                    'icon'      => [
                        'icon' => 'fal fa-chess-board',
                    ],
                    'meta'      => [
                        [
                            'label' => $portfolio->type,
                            'tooltip'   => __('Type'),
                        ],
                        [
                            'type'      => 'date',
                            'label'     => $portfolio->created_at,
                            'tooltip'   => __('Created At')
                        ],
                    ],
                ]
            ]
        );
    }

}
