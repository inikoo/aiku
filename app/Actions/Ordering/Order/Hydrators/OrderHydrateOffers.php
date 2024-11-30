<?php

/*
 * author Arya Permana - Kirin
 * created on 18-11-2024-14h-13m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Ordering\Order\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Discounts\TransactionHasOfferComponent;
use App\Models\Ordering\Order;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrderHydrateOffers
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
            'number_offers' => TransactionHasOfferComponent::where('order_id', $order->id)->distinct()->count('transaction_has_offer_components.offer_id'),
        ];


        $order->stats()->update($stats);
    }


}
