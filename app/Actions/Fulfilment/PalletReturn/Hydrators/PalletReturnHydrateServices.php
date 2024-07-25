<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Jul 2023 09:57:45 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn\Hydrators;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithEnumStats;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class PalletReturnHydrateServices extends HydrateModel
{
    use AsAction;
    use WithEnumStats;

    private PalletReturn $palletReturn;
    public function __construct(PalletReturn $palletReturn)
    {
        $this->palletReturn = $palletReturn;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->palletReturn->id))->dontRelease()];
    }

    public function handle(PalletReturn $palletReturn): void
    {
        $totalPrice = 0;
        $palletReturn->services()->each(function ($service) use (&$totalPrice, &$totalQuantity) {
            $totalPrice += $service->price * $service->pivot->quantity;
        });

        $stats = [
            'number_services'      => $palletReturn->services()->count(),
            'total_services_price' => $totalPrice,
            'total_price'          => $totalPrice + $palletReturn->stats->total_physical_goods_price
        ];

        $palletReturn->stats()->update($stats);
    }
}
