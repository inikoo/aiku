<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Apr 2024 09:00:21 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBillTransaction;

use App\Actions\Fulfilment\RecurringBill\CalculateRecurringBillTotals;
use App\Actions\Fulfilment\RecurringBill\Hydrators\RecurringBillHydrateTransactions;
use App\Actions\OrgAction;
use App\Enums\Fulfilment\FulfilmentTransaction\FulfilmentTransactionTypeEnum;
use App\Models\Catalogue\HistoricAsset;
use App\Models\Fulfilment\FulfilmentTransaction;
use App\Models\Fulfilment\Pallet;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\RecurringBillTransaction;
use App\Models\Fulfilment\StoredItem;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class StoreRecurringBillTransaction extends OrgAction
{
    public function handle(RecurringBill $recurringBill, Pallet|StoredItem|FulfilmentTransaction|HistoricAsset $item, array $modelData): RecurringBillTransaction
    {
        data_set($modelData, 'organisation_id', $recurringBill->organisation_id);
        data_set($modelData, 'group_id', $recurringBill->group_id);
        data_set($modelData, 'fulfilment_id', $recurringBill->fulfilment_id);
        data_set($modelData, 'fulfilment_customer_id', $recurringBill->fulfilment_customer_id);
        data_set($modelData, 'tax_category_id', $recurringBill->tax_category_id);


        if ($item instanceof FulfilmentTransaction) {
            if ($item->type == FulfilmentTransactionTypeEnum::SERVICE) {
                $type = 'Service';
            } else {
                $type = 'Product';
            }
            $assetId         = $item->asset->id;
            $historicAssetId = $item->asset->current_historic_asset_id;
            $totalQuantity   = $item->quantity;

            // todo add unit cost to the transaction
            $unitCost = $item->gross_amount / $item->quantity;
            data_set($modelData, 'item_id', $item->historicAsset->model_id);

        } elseif ($item instanceof HistoricAsset) {
            if ($item->model_type == 'Service') {
                $type = 'Service';
            } else {
                $type = 'Product';
            }
            $assetId         = $item->asset->id;
            $historicAssetId = $item->id;
            $totalQuantity   = Arr::pull($modelData, 'quantity');

            $unitCost = $item->price;
            data_set($modelData, 'item_id', $item->model_id);

        } else {
            $type            = class_basename($item);
            $assetId         = $item->rental->asset_id;
            $historicAssetId = $item->rental->asset->current_historic_asset_id;

            $totalQuantity = 0;

            if ($item instanceof StoredItem) {
                foreach ($item->pallets as $pallet) {
                    $totalQuantity += $pallet->pivot->quantity;
                }
            } else {
                $totalQuantity = 1;
            }

            $unitCost    = $item->rental->price;
            data_set($modelData, 'item_id', $item->id);
        }

        data_set($modelData, 'item_type', $type);
        data_set($modelData, 'asset_id', $assetId);
        data_set($modelData, 'historic_asset_id', $historicAssetId);
        data_set($modelData, 'quantity', $totalQuantity);
        data_set($modelData, 'unit_cost', $unitCost);


        /** @var RecurringBillTransaction $recurringBillTransaction */
        $recurringBillTransaction = $recurringBill->transactions()->create($modelData);


        if ($item instanceof Pallet) {
            $item->update(['current_recurring_bill_id' => $recurringBill->id]);
        }
        $recurringBillTransaction = CalculateRecurringBillTransactionDiscountPercentage::make()->action($recurringBillTransaction, $this->hydratorsDelay);
        $recurringBillTransaction = CalculateRecurringBillTransactionTemporalQuantity::make()->action($recurringBillTransaction);
        $recurringBillTransaction = CalculateRecurringBillTransactionAmounts::make()->action($recurringBillTransaction);
        $recurringBillTransaction = CalculateRecurringBillTransactionCurrencyExchangeRates::make()->action($recurringBillTransaction);

        CalculateRecurringBillTotals::dispatch($recurringBill);

        RecurringBillHydrateTransactions::dispatch($recurringBill)->delay($this->hydratorsDelay);


        return $recurringBillTransaction;
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if (!$this->has('start_date')) {
            $date = now();
            $this->set('start_date', $date);
        }
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'quantity' => ['sometimes', 'numeric'],
        ];
    }

    public function action(RecurringBill $recurringBill, Pallet|StoredItem|FulfilmentTransaction $item, array $modelData, int $hydratorsDelay = 0): RecurringBillTransaction
    {
        $this->asAction = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromShop($recurringBill->fulfilment->shop, $modelData);
        return $this->handle($recurringBill, $item, $this->validatedData);
    }

    public function asController(RecurringBill $recurringBill, HistoricAsset $historicAsset, ActionRequest $request)
    {
        $this->initialisationFromFulfilment($recurringBill->fulfilment, $request);
        $this->handle($recurringBill, $historicAsset, $this->validatedData);
    }

}
