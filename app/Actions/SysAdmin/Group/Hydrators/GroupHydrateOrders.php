<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 May 2024 19:26:53 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group\Hydrators;

use App\Enums\OMS\Order\OrderStateEnum;
use App\Models\SysAdmin\Group;
use App\Models\OMS\Order;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class GroupHydrateOrders
{
    use AsAction;

    private Group $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->group->id))->dontRelease()];
    }

    public function handle(Group $group): void
    {
        $stats = [
            'number_orders' => Order::count(),
        ];

        $stateCounts = Order::selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (OrderStateEnum::cases() as $orderState) {
            $stats['number_orders_state_'.$orderState->snake()] = Arr::get($stateCounts, $orderState->value, 0);
        }

        $group->salesStats()->update($stats);
    }


}
