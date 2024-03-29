<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\FulfilmentCustomer\HydrateFulfilmentCustomer;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Http\Resources\Fulfilment\PalletDeliveryResource;
use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Http\Resources\Json\JsonResource;
use Lorisleiva\Actions\ActionRequest;

class ConfirmPalletDelivery extends OrgAction
{
    use WithActionUpdate;


    public function handle(PalletDelivery $palletDelivery): PalletDelivery
    {
        if ($palletDelivery->state == PalletDeliveryStateEnum::IN_PROCESS) {
            SubmitPalletDelivery::run($palletDelivery);
        }

        $modelData['confirmed_at'] = now();
        $modelData['state']        = PalletDeliveryStateEnum::CONFIRMED;

        if(!$palletDelivery->{PalletDeliveryStateEnum::SUBMITTED->value.'_at'}) {
            $modelData[PalletDeliveryStateEnum::SUBMITTED->value.'_at'] = now();
        }

        foreach ($palletDelivery->pallets as $pallet) {
            $pallet->update([
                'state'     => PalletStateEnum::CONFIRMED
            ]);
        }

        HydrateFulfilmentCustomer::dispatch($palletDelivery->fulfilmentCustomer);


        return $this->update($palletDelivery, $modelData);
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");
    }

    public function jsonResponse(PalletDelivery $palletDelivery): JsonResource
    {
        return new PalletDeliveryResource($palletDelivery);
    }

    public function asController(PalletDelivery $palletDelivery, ActionRequest $request): PalletDelivery
    {
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $request);

        return $this->handle($palletDelivery);
    }
}
