<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 22:38:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment\Hydrators;

use App\Models\Fulfilment\Fulfilment;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class FulfilmentHydrateWarehouses
{
    use AsAction;

    private Fulfilment $fulfilment;

    public function __construct(Fulfilment $fulfilment)
    {
        $this->fulfilment = $fulfilment;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->fulfilment->id))->dontRelease()];
    }


    public function handle(Fulfilment $fulfilment): void
    {
        $fulfilment->update(
            [
                'number_warehouses'=> $fulfilment->warehouses()->count()

            ]
        );
    }


}
