<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 10:02:57 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Dispatch\DeliveryNote\Hydrators;

use App\Models\Dispatch\DeliveryNote;
use Lorisleiva\Actions\Concerns\AsAction;

class DeliveryNoteHydrateUniversalSearch
{
    use AsAction;


    public function handle(DeliveryNote $deliveryNote): void
    {
        $deliveryNote->universalSearch()->updateOrCreate(
            [],
            [
                'section'        => 'dispatch',
                'title'          => $deliveryNote->number,
                'description'    => $deliveryNote->email
            ]
        );
    }

}
