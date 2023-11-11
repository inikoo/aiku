<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 05 May 2023 12:02:33 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Organisation\Organisation\Hydrators;

use App\Actions\Traits\WithOrganisationJob;
use App\Enums\OMS\Order\OrderStateEnum;
use App\Models\OMS\Order;
use App\Models\Organisation\Organisation;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateOrders implements ShouldBeUnique
{
    use AsAction;
    use WithOrganisationJob;

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

        $organisation->crmStats->update($stats);
    }

    public function getJobUniqueId(Organisation $organisation): string
    {
        return $organisation->id;
    }
}
