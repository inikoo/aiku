<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Jul 2024 23:36:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentTransaction;

use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydrateTransactions;
use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydrateTransactions;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\FulfilmentTransaction;
use Lorisleiva\Actions\ActionRequest;

class DeleteFulfilmentTransaction extends OrgAction
{
    use WithActionUpdate;


    private Pallet $palletDeliveryTransaction;

    public function handle(FulfilmentTransaction $palletDeliveryTransaction): void
    {
        $palletDeliveryTransaction->delete();

        if ($palletDeliveryTransaction->parent_type == 'PalletDelivery') {
            PalletDeliveryHydrateTransactions::run($palletDeliveryTransaction->parent);
        } else {
            PalletReturnHydrateTransactions::run($palletDeliveryTransaction->parent);
        }
    }

    //todo authorisation

    public function fromRetina(FulfilmentTransaction $palletDeliveryTransaction, ActionRequest $request): void
    {
        $this->initialisationFromFulfilment($palletDeliveryTransaction->fulfilment, $request);

        $this->handle($palletDeliveryTransaction);
    }

    public function asController(FulfilmentTransaction $palletDeliveryTransaction, ActionRequest $actionRequest): void
    {
        $this->initialisationFromFulfilment($palletDeliveryTransaction->fulfilment, $actionRequest);

        $this->handle($palletDeliveryTransaction);
    }


    public function action(FulfilmentTransaction $palletDeliveryTransaction): void
    {
        $this->asAction = true;
        $this->initialisationFromFulfilment($palletDeliveryTransaction->fulfilment, []);

        $this->handle($palletDeliveryTransaction);
    }

}
