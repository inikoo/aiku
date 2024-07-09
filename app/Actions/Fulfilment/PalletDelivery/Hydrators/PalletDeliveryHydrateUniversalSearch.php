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
    public string $jobQueue = 'universal-search';

    public function handle(PalletDelivery $palletDelivery): void
    {
        $palletDelivery->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $palletDelivery->group_id,
                'organisation_id'   => $palletDelivery->organisation_id,
                'organisation_slug' => $palletDelivery->organisation->slug,
                'warehouse_id'      => $palletDelivery->warehouse_id,
                'warehouse_slug'    => $palletDelivery->warehouse->slug,
                'fulfilment_id'     => $palletDelivery->fulfilment_id,
                'fulfilment_slug'   => $palletDelivery->fulfilment->slug,
                'section'           => 'fulfilment',
                'title'             => $palletDelivery->reference,
                'slug'              => 'pad-'.$palletDelivery->slug,
            ]
        );
    }

}
