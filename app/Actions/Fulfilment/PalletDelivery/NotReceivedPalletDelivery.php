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
use App\Enums\Fulfilment\PalletDelivery\PalletDeliveryStateEnum;
use App\Models\Fulfilment\PalletDelivery;

class NotReceivedPalletDelivery extends OrgAction
{
    use WithActionUpdate;


    public function handle(PalletDelivery $palletDelivery): PalletDelivery
    {

        if($palletDelivery->state != PalletDeliveryStateEnum::RECEIVED) {
            abort(419);
        }

        $modelData['not_received_at'] = now();
        $modelData['state']           = PalletDeliveryStateEnum::NOT_RECEIVED;

        $palletDelivery= $this->update($palletDelivery, $modelData);
        HydrateFulfilmentCustomer::dispatch($palletDelivery->fulfilmentCustomer);

        SendPalletDeliveryNotification::dispatch($palletDelivery);

        return $palletDelivery;
    }


}
