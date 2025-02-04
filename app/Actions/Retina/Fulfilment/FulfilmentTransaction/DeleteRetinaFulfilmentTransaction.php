<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 Jan 2025 02:07:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\FulfilmentTransaction;

use App\Actions\Fulfilment\PalletDelivery\CalculatePalletDeliveryNet;
use App\Actions\Fulfilment\PalletDelivery\Hydrators\PalletDeliveryHydrateTransactions;
use App\Actions\Fulfilment\PalletReturn\CalculatePalletReturnNet;
use App\Actions\Fulfilment\PalletReturn\Hydrators\PalletReturnHydrateTransactions;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\FulfilmentTransaction;
use App\Models\Fulfilment\Pallet;
use Lorisleiva\Actions\ActionRequest;

class DeleteRetinaFulfilmentTransaction extends RetinaAction
{
    use WithActionUpdate;


    private Pallet $palletDeliveryTransaction;
    private bool $action = false;
    public function handle(FulfilmentTransaction $fulfilmentTransaction): void
    {
        $fulfilmentTransaction->delete();

        if ($fulfilmentTransaction->parent_type == 'PalletDelivery') {
            PalletDeliveryHydrateTransactions::run($fulfilmentTransaction->parent);
            CalculatePalletDeliveryNet::run($fulfilmentTransaction->parent);
        } else {
            PalletReturnHydrateTransactions::run($fulfilmentTransaction->parent);
            CalculatePalletReturnNet::run($fulfilmentTransaction->parent);
        }
    }

    //todo authorisation
    public function asController(FulfilmentTransaction $fulfilmentTransaction, ActionRequest $request): void
    {
        $this->initialisation($request);

        $this->handle($fulfilmentTransaction);
    }

    public function action(FulfilmentTransaction $fulfilmentTransaction): void
    {
        $this->initialisationFulfilmentActions($fulfilmentTransaction->fulfilmentCustomer, []);

        $this->handle($fulfilmentTransaction);
    }
}
