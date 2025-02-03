<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletReturn;

use App\Actions\Fulfilment\Fulfilment\Hydrators\FulfilmentHydratePalletReturns;
use App\Actions\Fulfilment\FulfilmentCustomer\Hydrators\FulfilmentCustomerHydratePalletReturns;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\Fulfilment\PalletReturn\Notifications\SendPalletReturnNotification;
use App\Actions\Fulfilment\PalletReturn\Search\PalletReturnRecordSearch;
use App\Actions\Helpers\SerialReference\GetSerialReference;
use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydratePalletReturns;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydratePalletReturns;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePalletReturns;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Http\Resources\Fulfilment\PalletReturnResource;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletReturn;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;
use Lorisleiva\Actions\ActionRequest;

class SubmitPalletReturn extends OrgAction
{
    use WithActionUpdate;


    private bool $sendNotifications = false;

    public function handle(PalletReturn $palletReturn, array $modelData): PalletReturn
    {
        $modelData['submitted_at'] = now();
        $modelData['confirmed_at'] = now();
        $modelData['state']                                       = PalletReturnStateEnum::CONFIRMED;


        foreach ($palletReturn->pallets as $pallet) {
            UpdatePallet::run($pallet, [
                'reference' => GetSerialReference::run(
                    container: $palletReturn->fulfilmentCustomer,
                    modelType: SerialReferenceModelEnum::PALLET
                ),
                'state'     => PalletStateEnum::REQUEST_RETURN_CONFIRMED,
                'status'    => PalletStatusEnum::RETURNING
            ]);

            $palletReturn->pallets()->syncWithoutDetaching([
                $pallet->id => [
                    'state' => $modelData['state']
                ]
            ]);
        }

        $palletReturn = $this->update($palletReturn, $modelData);

        GroupHydratePalletReturns::dispatch($palletReturn->group);
        OrganisationHydratePalletReturns::dispatch($palletReturn->organisation);
        WarehouseHydratePalletReturns::dispatch($palletReturn->warehouse);
        FulfilmentCustomerHydratePalletReturns::dispatch($palletReturn->fulfilmentCustomer);
        FulfilmentHydratePalletReturns::dispatch($palletReturn->fulfilment);


        if ($this->sendNotifications) {
            SendPalletReturnNotification::run($palletReturn);
        }
        PalletReturnRecordSearch::dispatch($palletReturn);

        return $palletReturn;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->authTo("fulfilment-shop.{$this->fulfilment->id}.edit");
    }

    public function jsonResponse(PalletReturn $palletReturn): JsonResource
    {
        return new PalletReturnResource($palletReturn);
    }

    public function asController(Organisation $organisation, FulfilmentCustomer $fulfilmentCustomer, PalletReturn $palletReturn, ActionRequest $request): PalletReturn
    {
        $this->sendNotifications = true;
        $this->initialisationFromFulfilment($fulfilmentCustomer->fulfilment, $request);

        return $this->handle($palletReturn, $this->validatedData);
    }

    public function action(PalletReturn $palletReturn, bool $sendNotification = false): PalletReturn
    {
        $this->asAction          = true;
        $this->sendNotifications = $sendNotification;
        $this->initialisationFromFulfilment($palletReturn->fulfilment, []);

        return $this->handle($palletReturn, []);
    }
}
