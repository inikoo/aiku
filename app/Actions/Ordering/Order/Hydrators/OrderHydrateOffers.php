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
                return $this->countOfferComponents($transaction);
            }),
            'number_offers' => $order->transactions()->sum(function ($transaction) {
                return $this->countOffers($transaction);
            }),
            'number_offer_campaigns' => $order->transactions()->sum(function ($transaction) {
                return $this->countOfferCampaigns($transaction);
            }),
        ];


        $order->stats()->update($stats);
    }

    public function countOfferComponents($transaction): int
    {
        return $transaction->offerComponents()
            ->distinct('offer_component_id')
            ->count('offer_component_id');
    }

    public function countOffers($transaction): int
    {
        return $transaction->offerComponents()
            ->distinct('offer_id')
            ->count('offer_id');
    }

    public function countOfferCampaigns($transaction): int
    {
        return $transaction->offerComponents()
            ->distinct('offer_campaigns_id')
            ->count('offer_campaigns_id');
    }

}
