<?php
/*
 * author Arya Permana - Kirin
 * created on 18-11-2024-13h-19m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Discounts\OfferComponent\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Discounts\OfferComponent;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OfferComponentHydrateOrders
{
    use AsAction;
    use WithEnumStats;

    private OfferComponent $offerComponent;
    public function __construct(OfferComponent $offerComponent)
    {
        $this->offerComponent = $offerComponent;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->offerComponent->id))->dontRelease()];
    }

    public function handle(OfferComponent $offerComponent): void
    {
        $stats = [
            'number_orders'   => $offerComponent->orderTransactions()
            ->with('model')
            ->get()
            ->pluck('model.order_id')
            ->unique()
            ->count()
            ->distinct('order_id')
            ->count('order_id'),
        ];


        $offerComponent->stats()->update($stats);
    }


}
