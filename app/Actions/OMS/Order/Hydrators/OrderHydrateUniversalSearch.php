<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:11 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\Order\Hydrators;

use App\Actions\Traits\WithOrganisationJob;
use App\Models\OMS\Order;
use Lorisleiva\Actions\Concerns\AsAction;

class OrderHydrateUniversalSearch
{
    use AsAction;
    use WithOrganisationJob;

    public function handle(Order $order): void
    {
        $order->universalSearch()->updateOrCreate(
            [],
            [
                'section'        => 'oms',
                'title'          => $order->number,
                'description'    => ''
            ]
        );
    }

}
