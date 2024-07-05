<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Jul 2024 23:36:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentTransaction;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\FulfilmentTransaction;

class UpdateFulfilmentTransaction extends OrgAction
{
    use WithActionUpdate;


    private Pallet $palletDeliveryTransaction;

    public function handle(FulfilmentTransaction $palletDeliveryTransaction, array $modelData): FulfilmentTransaction
    {
        return $this->update($palletDeliveryTransaction, $modelData, ['data']);
    }


    public function rules(): array
    {
        return [];
    }



    public function action(FulfilmentTransaction $palletDeliveryTransaction, array $modelData): FulfilmentTransaction
    {

        $this->asAction       = true;
        $this->initialisationFromFulfilment($palletDeliveryTransaction->fulfilment, $modelData);

        return $this->handle($palletDeliveryTransaction, $this->validatedData);
    }


}
