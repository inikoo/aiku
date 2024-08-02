<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Jul 2024 23:36:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentTransaction;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\FulfilmentTransaction;
use Lorisleiva\Actions\ActionRequest;

class UpdateFulfilmentTransaction extends OrgAction
{
    use WithActionUpdate;

    public function handle(FulfilmentTransaction $palletDeliveryTransaction, array $modelData): FulfilmentTransaction
    {
        $palletDeliveryTransaction =  $this->update($palletDeliveryTransaction, $modelData, ['data']);

        $palletDeliveryTransaction->refresh();

        $netAmount = $palletDeliveryTransaction->asset->price * $palletDeliveryTransaction->quantity;

        $this->update(
            $palletDeliveryTransaction,
            [
            'net_amount'              => $netAmount,
            'grp_net_amount'          => $netAmount * $palletDeliveryTransaction->grp_exchange,
            'org_net_amount'          => $netAmount * $palletDeliveryTransaction->org_exchange
        ]
        );

        $palletDeliveryTransaction->refresh();

        SetClausesInFulfilmentTransaction::run($palletDeliveryTransaction);

        return $palletDeliveryTransaction;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function fromRetina(FulfilmentTransaction $fulfilmentTransaction, ActionRequest $request): void
    {
        $this->initialisationFromFulfilment($fulfilmentTransaction->fulfilment, $request);

        $this->handle($fulfilmentTransaction, $this->validatedData);
    }

    public function asController(FulfilmentTransaction $fulfilmentTransaction, ActionRequest $actionRequest): void
    {
        $this->initialisationFromFulfilment($fulfilmentTransaction->fulfilment, $actionRequest);

        $this->handle($fulfilmentTransaction, $this->validatedData);
    }

    public function action(FulfilmentTransaction $palletDeliveryTransaction, array $modelData): FulfilmentTransaction
    {

        $this->asAction       = true;
        $this->initialisationFromFulfilment($palletDeliveryTransaction->fulfilment, $modelData);

        return $this->handle($palletDeliveryTransaction, $this->validatedData);
    }

}
