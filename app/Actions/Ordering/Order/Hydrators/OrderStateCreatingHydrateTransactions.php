<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 19:53:53 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order\Hydrators;

use App\Actions\Traits\WithEnumStats;

use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Ordering\Order;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrderStateCreatingHydrateTransactions
{
    use AsAction;
    use WithEnumStats;
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->order->id))->dontRelease()];
    }
    public function handle(Order $order): void
    {
        if($order->state !== OrderStateEnum::CREATING) {
            return;
        }

        $stats= [
            'number_items_at_creation' => $order->transactions()->count(),
        ];


        $order->stats()->update($stats);
    }

}
