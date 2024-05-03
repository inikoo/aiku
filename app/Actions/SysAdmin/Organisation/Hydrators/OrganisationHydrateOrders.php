<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Enums\OMS\Order\OrderStateEnum;
use App\Models\SysAdmin\Organisation;
use App\Models\OMS\Order;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateOrders
{
    use AsAction;


    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_orders' => Order::count(),
        ];

        $stateCounts = Order::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (OrderStateEnum::cases() as $orderState) {
            $stats['number_orders_state_' . $orderState->snake()] = Arr::get($stateCounts, $orderState->value, 0);
        }

        $organisation->salesIntervals()->update($stats);
    }


}
