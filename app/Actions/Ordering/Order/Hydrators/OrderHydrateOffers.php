<?php
/*
 * author Arya Permana - Kirin
 * created on 18-11-2024-14h-13m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Ordering\Order\Hydrators;

use App\Actions\Traits\WithEnumStats;
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
            'number_offer_components' => $order->transactions()->sum(function ($transaction) {
                return $transaction->countOfferComponents();
            }),
            'number_offers' => $order->transactions()->sum(function ($transaction) {
                return $transaction->countOffers();
            }),
            'number_offer_campaigns' => $order->transactions()->sum(function ($transaction) {
                return $transaction->countOfferCampaigns();
            }),
        ];


        $order->stats()->update($stats);
    }

}
