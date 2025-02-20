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
use App\Enums\Fulfilment\PalletReturn\PalletReturnItemStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Enums\Fulfilment\PalletReturn\PalletReturnTypeEnum;
use App\Enums\Helpers\SerialReference\SerialReferenceModelEnum;
use App\Models\Fulfilment\PalletReturn;

class SubmitPalletReturn extends OrgAction
{
    use WithActionUpdate;


    private bool $sendNotifications = false;

    public function handle(PalletReturn $palletReturn, array $modelData, bool $sendNotifications = false): PalletReturn
    {
        $modelData['submitted_at'] = now();
        $modelData['state']        = PalletReturnStateEnum::SUBMITTED;

        if ($palletReturn->type == PalletReturnTypeEnum::PALLET) {
            foreach ($palletReturn->pallets as $pallet) {
                UpdatePallet::run($pallet, [
                    'reference' => GetSerialReference::run(
                        container: $palletReturn->fulfilmentCustomer,
                        modelType: SerialReferenceModelEnum::PALLET
                    ),
                    'state'     => PalletStateEnum::REQUEST_RETURN_SUBMITTED,
                    'status'    => PalletStatusEnum::RETURNING
                ]);

                $palletReturn->pallets()->syncWithoutDetaching([
                    $pallet->id => [
                        'state' => PalletReturnItemStateEnum::SUBMITTED
                    ]
                ]);
            }
        }

        $palletReturn = $this->update($palletReturn, $modelData);

        GroupHydratePalletReturns::dispatch($palletReturn->group);
        OrganisationHydratePalletReturns::dispatch($palletReturn->organisation);
        WarehouseHydratePalletReturns::dispatch($palletReturn->warehouse);
        FulfilmentCustomerHydratePalletReturns::dispatch($palletReturn->fulfilmentCustomer);
        FulfilmentHydratePalletReturns::dispatch($palletReturn->fulfilment);


        if ($sendNotifications) {
            SendPalletReturnNotification::run($palletReturn);
        }
        PalletReturnRecordSearch::dispatch($palletReturn);

        return $palletReturn;
    }


    public function action(PalletReturn $palletReturn, bool $sendNotification = false): PalletReturn
    {
        $this->asAction          = true;
        $this->sendNotifications = $sendNotification;
        $this->initialisationFromFulfilment($palletReturn->fulfilment, []);

        return $this->handle($palletReturn, []);
    }
}
