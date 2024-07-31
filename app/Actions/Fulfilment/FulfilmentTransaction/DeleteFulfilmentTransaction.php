<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Jul 2024 23:36:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentTransaction;

use App\Actions\Fulfilment\PalletDelivery\CalculatePalletDeliveryNet;
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
            CalculatePalletDeliveryNet::run($palletDeliveryTransaction->parent);
        } else {
            PalletReturnHydrateTransactions::run($palletDeliveryTransaction->parent);
        }
    }

    //todo authorisation
    public function fromRetina(FulfilmentTransaction $fulfilmentTransaction, ActionRequest $request): void
    {
        $this->initialisationFromFulfilment($fulfilmentTransaction->fulfilment, $request);

        $this->handle($fulfilmentTransaction);
    }

    public function asController(FulfilmentTransaction $fulfilmentTransaction, ActionRequest $actionRequest): void
    {
        $this->initialisationFromFulfilment($fulfilmentTransaction->fulfilment, $actionRequest);

        $this->handle($fulfilmentTransaction);
    }


    public function action(FulfilmentTransaction $palletDeliveryTransaction): void
    {
        $this->asAction = true;
        $this->initialisationFromFulfilment($palletDeliveryTransaction->fulfilment, []);

        $this->handle($palletDeliveryTransaction);
    }

}
