<?php

/*
 * author Arya Permana - Kirin
 * created on 16-01-2025-13h-42m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Retina\Storage\PalletDelivery;

use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Events\BroadcastPalletDeliveryTimeline;
use App\Models\CRM\WebUser;
use App\Models\Fulfilment\PalletDelivery;
use Lorisleiva\Actions\ActionRequest;

class UpdateRetinaPalletDeliveryTimeline extends RetinaAction
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

    public function authorize(ActionRequest $request): bool
    {
        if ($this->action) {
            return true;
        }

        if ($request->user() instanceof WebUser) {
            return true;
        }

        return false;
    }

    public function asController(PalletDelivery $palletDelivery, ActionRequest $request): PalletDelivery
    {
        $this->initialisation($request);

        return $this->handle($palletDelivery, $this->validatedData);
    }
}
