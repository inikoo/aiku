<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 27 Nov 2024 18:19:13 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Discounts\TransactionHasOfferComponent;
use App\Models\Ordering\Order;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrderHydrateOfferComponents
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
        $stats = [
            'number_offer_components' => TransactionHasOfferComponent::where('order_id', $order->id)->distinct()->count('transaction_has_offer_components.offer_component_id'),
        ];


        $order->stats()->update($stats);
    }


}
