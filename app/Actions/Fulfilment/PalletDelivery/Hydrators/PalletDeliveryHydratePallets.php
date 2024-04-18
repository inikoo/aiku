<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Jul 2023 09:57:45 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\Hydrators;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithEnumStats;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class PalletDeliveryHydratePallets extends HydrateModel
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
            'number_pallets' => Pallet::where('pallet_delivery_id', $palletDelivery->id)->count()
        ];

        $stats=array_merge($stats, $this->getEnumStats(
            model:'pallets',
            field: 'state',
            enum: PalletStateEnum::class,
            models: Pallet::class,
            where: function ($q) use ($palletDelivery) {
                $q->where('pallet_delivery_id', $palletDelivery->id);
            }
        ));

        $stats=array_merge($stats, $this->getEnumStats(
            model:'pallets',
            field: 'status',
            enum: PalletStatusEnum::class,
            models: Pallet::class,
            where: function ($q) use ($palletDelivery) {
                $q->where('pallet_delivery_id', $palletDelivery->id);
            }
        ));

        $stats=array_merge($stats, $this->getEnumStats(
            model:'pallets',
            field: 'type',
            enum: PalletTypeEnum::class,
            models: Pallet::class,
            where: function ($q) use ($palletDelivery) {
                $q->where('pallet_delivery_id', $palletDelivery->id);
            }
        ));

        $palletDelivery->update(Arr::only($stats, ['number_pallets']));
        $palletDelivery->stats()->update($stats);
    }
}
