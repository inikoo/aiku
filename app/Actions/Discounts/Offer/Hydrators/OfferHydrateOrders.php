<?php
/*
 * author Arya Permana - Kirin
 * created on 18-11-2024-13h-19m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Discounts\OfferComponent\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Discounts\Offer\OfferStateEnum;
use App\Models\Discounts\Offer;
use App\Models\Discounts\OfferCampaign;
use App\Models\Discounts\OfferComponent;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OfferHydrateOrders
{
    use AsAction;
    use WithEnumStats;

    private Offer $offer;
    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->offer->id))->dontRelease()];
    }

    public function handle(Offer $offer): void
    {
        $stats = [
            'number_orders'   => $offer->orderTransactions()
            ->with('model')
            ->get()
            ->pluck('model.order_id')
            ->unique()
            ->count()
            ->distinct('order_id')
            ->count('order_id'),
        ];


        $offer->stats()->update($stats);
    }


}
