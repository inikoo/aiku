<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Jul 2023 09:57:45 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\Hydrators;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithEnumStats;
use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class PalletDeliveryHydratePhysicalGoods extends HydrateModel
{
    use AsAction;
    use WithEnumStats;

    private PalletDelivery $palletDelivery;
    public function __construct(PalletDelivery $palletDelivery)
    {
        $this->palletDelivery = $palletDelivery;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->palletDelivery->id))->dontRelease()];
    }

    public function handle(PalletDelivery $palletDelivery): void
    {
        $stats = [
            'number_physical_goods' => $palletDelivery->services()->count()
        ];

        $palletDelivery->stats()->update($stats);
    }
}
