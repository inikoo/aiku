<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Dispatch\Shipment\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Dispatch\Shipment;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class ShipmentHydrateUniversalSearch implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Shipment $shipment): void
    {
        $shipment->universalSearch()->create(
            [
                'primary_term'   => $shipment->code,
                'secondary_term' => $shipment->tracking
            ]
        );
    }

    public function getJobUniqueId(Shipment $shipment): int
    {
        return $shipment->id;
    }
}
