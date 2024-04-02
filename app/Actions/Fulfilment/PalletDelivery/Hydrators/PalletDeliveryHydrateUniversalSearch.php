<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Fulfilment\PalletDelivery\Hydrators;

use App\Models\Fulfilment\PalletDelivery;
use Lorisleiva\Actions\Concerns\AsAction;

class PalletDeliveryHydrateUniversalSearch
{
    use AsAction;


    public function handle(PalletDelivery $palletDelivery): void
    {
        $palletDelivery->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'        => $palletDelivery->group_id,
                'organisation_id' => $palletDelivery->organisation_id,
                'warehouse_id'    => $palletDelivery->warehouse_id,
                'section'         => 'fulfilment',
                'title'           => $palletDelivery->reference,
                'slug'            => 'pad-'.$palletDelivery->slug,
            ]
        );
    }

}
