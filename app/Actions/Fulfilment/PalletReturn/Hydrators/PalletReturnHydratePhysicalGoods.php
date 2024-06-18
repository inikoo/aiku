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

class PalletReturnHydratePhysicalGoods extends HydrateModel
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
        $palletReturn->physicalGoods->each(function ($physicalGood) use (&$totalPrice, &$totalQuantity) {
            $totalPrice += $physicalGood->price * $physicalGood->pivot->quantity;
        });

        $stats = [
            'number_physical_goods'      => $palletReturn->physicalGoods()->count(),
            'total_physical_goods_price' => $totalPrice,
            'total_price'                => $totalPrice + $palletReturn->stats->total_services_price
        ];

        $palletReturn->stats()->update($stats);
    }
}
