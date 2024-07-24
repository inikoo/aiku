<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\Pallet\Search\PalletRecordSearch;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Http\Resources\Fulfilment\PalletDeliveryResource;
use App\Models\Fulfilment\PalletDelivery;
use Illuminate\Http\Resources\Json\JsonResource;
use Lorisleiva\Actions\ActionRequest;

class ConfirmPalletDelivery extends OrgAction
{
    use WithActionUpdate;
    private PalletDelivery $palletDelivery;
    private bool $sendNotifications=false;

    public function handle(PalletDelivery $palletDelivery): PalletDelivery
    {
        if ($palletDelivery->state == PalletDeliveryStateEnum::IN_PROCESS) {
            SubmitPalletDelivery::run($palletDelivery);
        }

        foreach ($palletDelivery->pallets as $pallet) {
            UpdatePallet::run($pallet, [
                'reference' => GetSerialReference::run(
                    container: $palletDelivery->fulfilmentCustomer,
                    modelType: SerialReferenceModelEnum::PALLET
                ),
                'state'  => PalletStateEnum::SUBMITTED,
                'status' => PalletStatusEnum::RECEIVING
            ]);
            $pallet->generateSlug();

            PalletRecordSearch::run($pallet);
        }

        $modelData['confirmed_at'] = now();
        $modelData['state']        = PalletDeliveryStateEnum::CONFIRMED;

        if(!$palletDelivery->{PalletDeliveryStateEnum::SUBMITTED->value.'_at'}) {
            $modelData[PalletDeliveryStateEnum::SUBMITTED->value.'_at'] = now();
        }

        foreach ($palletDelivery->pallets as $pallet) {
            UpdatePallet::run($pallet, [
                'state'     => PalletStateEnum::CONFIRMED,
                'status'    => PalletStatusEnum::RECEIVING,
            ]);
        }

        $palletDelivery = $this->update($palletDelivery, $modelData);
        if($this->sendNotifications) {
            SendPalletDeliveryNotification::dispatch($palletDelivery);
        }
        return $palletDelivery;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }

        if(! in_array($this->palletDelivery->state, [PalletDeliveryStateEnum::SUBMITTED,
            PalletDeliveryStateEnum::IN_PROCESS])) {
            return false;
        }

        return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function jsonResponse(PalletDelivery $palletDelivery): JsonResource
    {
        return new PalletDeliveryResource($palletDelivery);
    }

    public function asController(PalletDelivery $palletDelivery, ActionRequest $request): PalletDelivery
    {
        $this->palletDelivery   = $palletDelivery;
        $this->sendNotifications=true;
        $this->initialisationFromFulfilment($palletDelivery->fulfilment, $request);
        return $this->handle($palletDelivery);
    }

    public function action(PalletDelivery $palletDelivery, bool $sendNotification=false): PalletDelivery
    {
        $this->asAction          = true;
        $this->palletDelivery    = $palletDelivery;
        $this->sendNotifications = $sendNotification;

        $this->initialisation($palletDelivery->organisation, []);
        return $this->handle($palletDelivery);
    }
}
