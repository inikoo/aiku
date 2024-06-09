<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:58:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Ordering\Order\OrderHandingTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Order\OrderStatusEnum;
use App\Models\CRM\Customer;
use App\Models\Ordering\Order;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateOrders
{
    use AsAction;
    use WithEnumStats;

    private Customer $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->customer->id))->dontRelease()];
    }

    public function handle(Customer $customer): void
    {
        $stats = [
            'number_orders' => $customer->orders->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'orders',
                field: 'state',
                enum: OrderStateEnum::class,
                models: Order::class,
                where: function ($q) use ($customer) {
                    $q->where('customer_id', $customer->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'orders',
                field: 'status',
                enum: OrderStatusEnum::class,
                models: Order::class,
                where: function ($q) use ($customer) {
                    $q->where('customer_id', $customer->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'orders',
                field: 'handing_type',
                enum: OrderHandingTypeEnum::class,
                models: Order::class,
                where: function ($q) use ($customer) {
                    $q->where('customer_id', $customer->id);
                }
            )
        );

        $customer->stats()->update($stats);
    }

}
