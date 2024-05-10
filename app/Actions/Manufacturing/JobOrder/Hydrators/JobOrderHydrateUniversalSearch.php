<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Manufacturing\JobOrder\Hydrators;


use App\Models\Fulfilment\PalletDelivery;
use App\Models\Manufacturing\JobOrder;
use Lorisleiva\Actions\Concerns\AsAction;

class JobOrderHydrateUniversalSearch
{
    use AsAction;


    public function handle(JobOrder $jobOrder): void
    {
        $jobOrder->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $jobOrder->group_id,
                'organisation_id'   => $jobOrder->organisation_id,
                'organisation_slug' => $jobOrder->organisation->slug,
                'section'           => 'manufacture',
                'title'             => $jobOrder->reference,
                'slug'              => 'pad-'.$jobOrder->slug,
            ]
        );
    }

}
