<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Jul 2024 23:36:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment;

use App\Actions\Fulfilment\PalletDelivery\CalculatePalletDeliveryNet;
use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydrateTransactions;
use App\Actions\Fulfilment\PalletReturn\CalculatePalletReturnNet;
use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydrateTransactions;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\FulfilmentTransaction;
use Lorisleiva\Actions\ActionRequest;

class DeleteRetinaFulfilmentTransaction extends RetinaAction
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
            CalculatePalletReturnNet::run($palletDeliveryTransaction->parent);
        }
    }

    //todo authorisation
    public function asController(FulfilmentTransaction $fulfilmentTransaction, ActionRequest $request): void
    {
        $this->initialisation($request);

        $this->handle($fulfilmentTransaction);
    }
}
