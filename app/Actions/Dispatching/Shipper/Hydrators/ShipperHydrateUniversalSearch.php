<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Dispatching\Shipper\Hydrators;

use App\Models\Dispatching\Shipper;
use Lorisleiva\Actions\Concerns\AsAction;

class ShipperHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

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
