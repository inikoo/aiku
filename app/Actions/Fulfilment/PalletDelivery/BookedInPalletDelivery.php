<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 24 Jan 2024 16:14:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery;

use App\Actions\Fulfilment\FulfilmentCustomer\HydrateFulfilmentCustomer;
use App\Actions\Fulfilment\Pallet\UpdatePallet;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Models\Fulfilment\PalletDelivery;
use Lorisleiva\Actions\ActionRequest;

class BookedInPalletDelivery extends OrgAction
{
    use WithActionUpdate;


    public function handle(PalletDelivery $palletDelivery): PalletDelivery
    {
        $modelData['booked_in_at'] = now();
        $modelData['state']        = PalletDeliveryStateEnum::BOOKED_IN;


        foreach ($palletDelivery->pallets as $pallet) {
            if ($pallet->state == PalletStateEnum::BOOKED_IN) {
                UpdatePallet::run($pallet, [
                    'state'  => PalletStateEnum::STORING,
                    'status' => PalletStatusEnum::STORING
                ]);
            }
        }


        $palletDelivery = $this->update($palletDelivery, $modelData);
        HydrateFulfilmentCustomer::dispatch($palletDelivery->fulfilmentCustomer);

        SendPalletDeliveryNotification::dispatch($palletDelivery);

        return $palletDelivery;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");
    }


}
