<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 05 May 2023 12:02:33 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Tenant\Hydrators;

use App\Actions\WithTenantJob;
use App\Enums\Sales\Order\OrderStateEnum;
use App\Models\Sales\Order;
use App\Models\Tenancy\Tenant;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class TenantHydrateOrders implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Tenant $tenant): void
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

        $tenant->salesStats->update($stats);
    }

    public function getJobUniqueId(Tenant $tenant): string
    {
        return $tenant->id;
    }
}
