<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Dispatch\Shipper\Hydrators;

use App\Models\Dispatch\Shipper;
use Lorisleiva\Actions\Concerns\AsAction;

class ShipperHydrateUniversalSearch
{
    use AsAction;


    public function handle(Shipper $shipper): void
    {
        $shipper->universalSearch()->updateOrCreate(
            [],
            [
                'section'           => 'dispatch',
                'title'             => $shipper->name,
            ]
        );
    }

}
