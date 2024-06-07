<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Dispatching\DeliveryNote\Hydrators;

use App\Models\Dispatching\DeliveryNote;
use Lorisleiva\Actions\Concerns\AsAction;

class DeliveryNoteHydrateUniversalSearch
{
    use AsAction;


    public function handle(DeliveryNote $deliveryNote): void
    {
        $deliveryNote->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $deliveryNote->group_id,
                'organisation_id'   => $deliveryNote->organisation_id,
                'organisation_slug' => $deliveryNote->organisation->slug,
                'shop_id'           => $deliveryNote->shop_id,
                'shop_slug'         => $deliveryNote->shop->slug,
                'customer_id'       => $deliveryNote->customer_id,
                'customer_slug'     => $deliveryNote->customer->slug,
                'section'           => 'dispatch',
                'title'             => $deliveryNote->number,
                'description'       => $deliveryNote->email
            ]
        );
    }

}
