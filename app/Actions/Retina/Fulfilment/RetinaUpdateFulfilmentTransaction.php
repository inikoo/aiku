<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Jul 2024 23:36:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment;

use App\Actions\Fulfilment\FulfilmentTransaction\SetClausesInFulfilmentTransaction;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\FulfilmentTransaction;
use Lorisleiva\Actions\ActionRequest;

class RetinaUpdateFulfilmentTransaction extends RetinaAction
{
    use WithActionUpdate;

    /**
     * @var true
     */
    private bool $asAction;

    public function handle(FulfilmentTransaction $palletDeliveryTransaction, array $modelData): FulfilmentTransaction
    {
        $palletDeliveryTransaction =  $this->update($palletDeliveryTransaction, $modelData, ['data']);

        $palletDeliveryTransaction->refresh();

        $netAmount = $palletDeliveryTransaction->asset->price * $palletDeliveryTransaction->quantity;

        $this->update(
            $palletDeliveryTransaction,
            [
            'net_amount'              => $netAmount,
            'gross_amount'            => $netAmount,
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

    public function asController(FulfilmentTransaction $fulfilmentTransaction, ActionRequest $request): FulfilmentTransaction
    {
        $this->initialisation($request);

        return $this->handle($fulfilmentTransaction, $this->validatedData);
    }
}
