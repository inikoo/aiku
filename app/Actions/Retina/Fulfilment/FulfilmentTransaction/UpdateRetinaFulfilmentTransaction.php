<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 Jan 2025 02:07:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Fulfilment\FulfilmentTransaction;

use App\Actions\Fulfilment\FulfilmentTransaction\SetClausesInFulfilmentTransaction;
use App\Actions\RetinaAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\FulfilmentTransaction;
use Lorisleiva\Actions\ActionRequest;

class UpdateRetinaFulfilmentTransaction extends RetinaAction
{
    use WithActionUpdate;

    /**
     * @var true
     */
    private bool $action = false;

    public function handle(FulfilmentTransaction $fulfilmentTransaction, array $modelData): FulfilmentTransaction
    {
        $fulfilmentTransaction =  $this->update($fulfilmentTransaction, $modelData, ['data']);

        $fulfilmentTransaction->refresh();

        $netAmount = $fulfilmentTransaction->asset->price * $fulfilmentTransaction->quantity;

        $this->update(
            $fulfilmentTransaction,
            [
            'net_amount'              => $netAmount,
            'gross_amount'            => $netAmount,
            'grp_net_amount'          => $netAmount * $fulfilmentTransaction->grp_exchange,
            'org_net_amount'          => $netAmount * $fulfilmentTransaction->org_exchange
        ]
        );

        $fulfilmentTransaction->refresh();

        SetClausesInFulfilmentTransaction::run($fulfilmentTransaction);

        return $fulfilmentTransaction;
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

    public function action(FulfilmentTransaction $fulfilmentTransaction, array $modelData): FulfilmentTransaction
    {
        $this->action = true;
        $this->initialisationFulfilmentActions($fulfilmentTransaction->fulfilmentCustomer, $modelData);
        return $this->handle($fulfilmentTransaction, $modelData);
    }
}
