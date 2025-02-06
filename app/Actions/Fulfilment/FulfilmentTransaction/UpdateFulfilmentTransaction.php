<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Jul 2024 23:36:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentTransaction;

use App\Actions\Fulfilment\RecurringBillTransaction\UpdateRecurringBillTransaction;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\FulfilmentTransaction;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateFulfilmentTransaction extends OrgAction
{
    use WithActionUpdate;


    public function handle(FulfilmentTransaction $fulfilmentTransaction, array $modelData, bool $isRecurringBillTransactionUpdated = false): FulfilmentTransaction
    {
        if (Arr::exists($modelData, 'net_amount')) {
            $netAmount = Arr::get($modelData, 'net_amount');
            $quantity = $netAmount / $fulfilmentTransaction->asset->price;
            data_set($modelData, 'quantity', $quantity);
        } else {
            $fulfilmentTransaction->refresh();
            $netAmount = $fulfilmentTransaction->asset->price * $fulfilmentTransaction->quantity;
        }

        $fulfilmentTransaction = $this->update($fulfilmentTransaction, $modelData, ['data']);

        if ($fulfilmentTransaction->recurringBillTransaction && !$isRecurringBillTransactionUpdated) {
            UpdateRecurringBillTransaction::make()->action($fulfilmentTransaction->recurringBillTransaction, $modelData, true);
        }

        $this->update(
            $fulfilmentTransaction,
            [
                'net_amount'     => $netAmount,
                'gross_amount'   => $netAmount,
                'grp_net_amount' => $netAmount * $fulfilmentTransaction->grp_exchange,
                'org_net_amount' => $netAmount * $fulfilmentTransaction->org_exchange
            ]
        );

        $fulfilmentTransaction->refresh();

        if (!Arr::exists($modelData, 'net_amount')) {
            SetClausesInFulfilmentTransaction::run($fulfilmentTransaction);
        }

        return $fulfilmentTransaction;
    }
    public function rules(): array
    {
        return [
            'quantity' => ['sometimes', 'numeric', 'min:0'],
            'net_amount' => ['sometimes', 'numeric', 'min:0'],
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

    public function action(FulfilmentTransaction $palletDeliveryTransaction, array $modelData, bool $isRecurringBillTransactionUpdated = false): FulfilmentTransaction
    {

        $this->asAction       = true;
        $this->initialisationFromFulfilment($palletDeliveryTransaction->fulfilment, $modelData);

        return $this->handle($palletDeliveryTransaction, $this->validatedData, $isRecurringBillTransactionUpdated);
    }
}
