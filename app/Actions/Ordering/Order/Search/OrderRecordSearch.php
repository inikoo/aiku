<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 10 Sept 2024 23:00:24 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order\Search;

use App\Models\Ordering\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class OrderRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Order $order): void
    {
        if ($order->trashed()) {
            $order->universalSearch()->delete();
            return;
        }

        $order->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $order->group_id,
                'organisation_id'   => $order->organisation_id,
                'organisation_slug' => $order->organisation->slug,
                'shop_id'           => $order->shop_id,
                'shop_slug'         => $order->shop->slug,
                'customer_id'       => $order->customer_id,
                'customer_slug'     => $order->customer->slug,
                'sections'          => ['ordering'],
                'haystack_tier_1'   => $order->reference,
            ]
        );
    }

}
