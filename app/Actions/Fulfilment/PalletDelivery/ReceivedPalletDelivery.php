<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\FulfilmentCustomer\HydrateFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\SetPalletRental;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Http\Resources\Fulfilment\PalletDeliveryResource;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Market\Rental;
use Illuminate\Http\Resources\Json\JsonResource;
use Lorisleiva\Actions\ActionRequest;

class ReceivedPalletDelivery extends OrgAction
{
    use WithActionUpdate;
    private PalletDelivery $palletDelivery;

    public function handle(PalletDelivery $palletDelivery, array $modelData = []): PalletDelivery
    {
        $modelData['received_at'] = now();
        $modelData['state']       = PalletDeliveryStateEnum::RECEIVED;

        foreach ($palletDelivery->pallets as $pallet) {



            UpdatePallet::run($pallet, [
                'state'     => PalletStateEnum::RECEIVED,
                'status'    => PalletStatusEnum::RECEIVING,
            ]);

            $pallet->generateSlug();
            $pallet->save();

            /** @var Rental $rental */
            $rental = $this->organisation->rentals()
                ->where('auto_assign_asset', class_basename(Pallet::class))
                ->where('auto_assign_asset_type', $pallet->type->value)
                ->first();

            if($rental) {
                SetPalletRental::run($pallet, [
                    'rental_id' => $rental->id
                ]);
            }

        }

        $palletDelivery = $this->update($palletDelivery, $modelData);




        HydrateFulfilmentCustomer::dispatch($palletDelivery->fulfilmentCustomer);
        SendPalletDeliveryNotification::run($palletDelivery);

        return $palletDelivery;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->palletDelivery->state != PalletDeliveryStateEnum::CONFIRMED) {
            return false;
        }

        if($this->asAction) {
            return true;
        }

        if(PalletDeliveryStateEnum::CONFIRMED->value) {
            return false;
        }

        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");
    }

    public function jsonResponse(PalletDelivery $palletDelivery): JsonResource
    {
        return new PalletDeliveryResource($palletDelivery);
    }

    public function asController(PalletDelivery $palletDelivery, ActionRequest $request): PalletDelivery
    {
        $this->palletDelivery = $palletDelivery;
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $request);

        return $this->handle($palletDelivery, $this->validatedData);
    }

    public function action(PalletDelivery $palletDelivery): PalletDelivery
    {
        $this->asAction = true;
        $this->initialisation($palletDelivery->organisation, []);
        return $this->handle($palletDelivery);
    }
}
