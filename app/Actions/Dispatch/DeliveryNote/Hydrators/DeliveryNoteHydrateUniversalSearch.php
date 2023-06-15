<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Dispatch\DeliveryNote\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Dispatch\DeliveryNote;
use Lorisleiva\Actions\Concerns\AsAction;

class DeliveryNoteHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(DeliveryNote $deliveryNote): void
    {
        $deliveryNote->universalSearch()->create(
            [
                'primary_term'   => $deliveryNote->number,
                'secondary_term' => $deliveryNote->email
            ]
        );
    }

}
