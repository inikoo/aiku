<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 25 Jan 2024 16:42:23 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class FulfilmentCustomerHydratePalletReturns
{
    use AsAction;
    use WithEnumStats;

    private FulfilmentCustomer $fulfilmentCustomer;
    public function __construct(FulfilmentCustomer $fulfilmentCustomer)
    {
        $this->fulfilmentCustomer = $fulfilmentCustomer;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->fulfilmentCustomer->id))->dontRelease()];
    }

    public function handle(FulfilmentCustomer $fulfilmentCustomer): void
    {
        $stats = [
            'number_pallet_returns' => PalletReturn::where('fulfilment_customer_id', $fulfilmentCustomer->id)->count()
        ];

        $stats=array_merge($stats, $this->getEnumStats(
            model:'pallet_returns',
            field: 'state',
            enum: PalletReturnStateEnum::class,
            models: PalletReturn::class,
            where: function ($q) use ($fulfilmentCustomer) {
                $q->where('fulfilment_customer_id', $fulfilmentCustomer->id);
            }
        ));

        $fulfilmentCustomer->update($stats);
    }
}
