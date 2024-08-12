<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 06 Aug 2024 16:04:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\TopUp\Search;

use App\Models\Accounting\TopUp;
use Lorisleiva\Actions\Concerns\AsAction;

class TopUpRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(TopUp $topUp): void
    {
        $topUp->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $topUp->group_id,
                'organisation_id'   => $topUp->organisation_id,
                'organisation_slug' => $topUp->organisation->slug,
                'shop_id'           => $topUp->shop_id,
                'shop_slug'         => $topUp->shop->slug,
                'customer_id'       => $topUp->customer_id,
                'customer_slug'     => $topUp->customer->slug,
                'payment_id'        => $topUp->payment_id,
                'payment_slug'      => $topUp->payment->slug,
                'sections'          => ['accounting'],
                'haystack_tier_1'   => $topUp->slug,
                'keyword'           => $topUp->number,
                'keyword_2'         => $topUp->slug
            ]
        );
    }

}
