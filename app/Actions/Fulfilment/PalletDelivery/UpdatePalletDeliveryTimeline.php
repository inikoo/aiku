<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Events\BroadcastPalletDeliveryTimeline;
use App\Models\Fulfilment\PalletDelivery;
use Lorisleiva\Actions\ActionRequest;

class UpdatePalletDeliveryTimeline extends OrgAction
{
    use WithActionUpdate;


    public function handle(PalletDelivery $palletDelivery, array $modelData): PalletDelivery
    {
        if ($palletDelivery->pallets()->count() === 0) {
            abort(404, 'Pallets not found');
        }

        match ($modelData['state']) {
            PalletDeliveryStateEnum::IN_PROCESS->value          => $modelData['in_process_at']                = now(),
            PalletDeliveryStateEnum::SUBMITTED->value           => $modelData['submitted_at']                 = now(),
            PalletDeliveryStateEnum::CONFIRMED->value           => $modelData['confirmed_at']                 = now(),
            PalletDeliveryStateEnum::RECEIVED->value            => $modelData['received_at']                  = now(),
            PalletDeliveryStateEnum::BOOKED_IN->value           => $modelData['booked_in_at']                 = now(),
            default                                             => null
        };

        $palletDelivery->pallets()->update([
            'state' => match ($modelData['state']) {
                PalletDeliveryStateEnum::IN_PROCESS->value          => PalletStateEnum::IN_PROCESS,
                PalletDeliveryStateEnum::SUBMITTED->value           => PalletStateEnum::SUBMITTED,
                PalletDeliveryStateEnum::CONFIRMED->value           => PalletStateEnum::CONFIRMED,
                PalletDeliveryStateEnum::RECEIVED->value            => PalletStateEnum::RECEIVED,
                PalletDeliveryStateEnum::BOOKED_IN->value           => PalletStateEnum::BOOKED_IN,
                default                                             => null
            }
        ]);

        $this->update($palletDelivery, $modelData);

        BroadcastPalletDeliveryTimeline::dispatch($palletDelivery->group, $palletDelivery);

        return $palletDelivery;
    }

    public function fromRetina(PalletDelivery $palletDelivery, ActionRequest $request): PalletDelivery
    {
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $request);

        return $this->handle($palletDelivery, $this->validatedData);
    }
}
