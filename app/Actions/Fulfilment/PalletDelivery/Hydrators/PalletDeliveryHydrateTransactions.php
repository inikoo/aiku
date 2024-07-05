<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Jul 2023 09:57:45 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\Hydrators;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithEnumStats;
use App\Enums\Fulfilment\FulfilmentTransaction\FulfilmentTransactionTypeEnum;
use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class PalletDeliveryHydrateTransactions extends HydrateModel
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
            'number_transactions'   => $palletDelivery->transactions()->count(),
            'number_services'       => $palletDelivery->transactions()->where('type', FulfilmentTransactionTypeEnum::SERVICE)->count(),
            'number_physical_goods' => $palletDelivery->transactions()->where('type', FulfilmentTransactionTypeEnum::PRODUCT)->count()
        ];



        $palletDelivery->stats()->update($stats);

    }
}
