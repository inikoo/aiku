<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:32 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Transaction;

use App\Actions\Ordering\Order\CalculateOrderTotalAmounts;
use App\Actions\Ordering\Order\Hydrators\OrderHydrateTransactions;
use App\Actions\OrgAction;
use App\Actions\Traits\WithOrderExchanges;
use App\Enums\Ordering\Transaction\TransactionFailStatusEnum;
use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Enums\Ordering\Transaction\TransactionStatusEnum;
use App\Models\Catalogue\HistoricAsset;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreTransaction extends OrgAction
{
    use WithOrderExchanges;


    public function handle(Order $order, HistoricAsset $historicAsset, array $modelData): Transaction
    {
        data_set($modelData, 'tax_category_id', $order->tax_category_id, overwrite: false);


        $net = $historicAsset->price * Arr::get($modelData, 'quantity_ordered');
        $gross = $historicAsset->price * Arr::get($modelData, 'quantity_ordered');

        data_set($modelData, 'shop_id', $order->shop_id);
        data_set($modelData, 'customer_id', $order->customer_id);
        data_set($modelData, 'group_id', $order->group_id);
        data_set($modelData, 'organisation_id', $order->organisation_id);
        data_set($modelData, 'historic_asset_id', $historicAsset->id);
        data_set($modelData, 'asset_id', $historicAsset->asset_id);

        data_set($modelData, 'date', now(), overwrite: false);
        data_set($modelData, 'submitted_at', $order->submitted_at, overwrite: false);
        data_set($modelData, 'gross_amount', $gross);
        data_set($modelData, 'net_amount', $net);
        data_set($modelData, 'state', TransactionStateEnum::CREATING);
        data_set($modelData, 'status', TransactionStatusEnum::CREATING);


        $modelData = $this->processExchanges($modelData, $order->shop);


        data_set($modelData, 'asset_type', $historicAsset->model_type);


        /** @var Transaction $transaction */
        $transaction = $order->transactions()->create($modelData);


        if ($this->strict) {
            CalculateOrderTotalAmounts::run($order);
            OrderHydrateTransactions::dispatch($order);
        }

        return $transaction;
    }

    public function rules(): array
    {
        $rules = [
            'quantity_ordered'    => ['required', 'numeric', 'min:0'],
            'quantity_bonus'      => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_dispatched' => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_fail'       => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_cancelled'  => ['sometimes', 'sometimes', 'numeric', 'min:0'],
            'source_id'           => ['sometimes', 'string'],
            'state'               => ['sometimes', Rule::enum(TransactionStateEnum::class)],
            'status'              => ['sometimes', Rule::enum(TransactionStatusEnum::class)],
            'fail_status'         => ['sometimes', 'nullable', Rule::enum(TransactionFailStatusEnum::class)],
            'gross_amount'        => ['sometimes', 'numeric'],
            'net_amount'          => ['sometimes', 'numeric'],
            'org_exchange'        => ['sometimes', 'numeric'],
            'grp_exchange'        => ['sometimes', 'numeric'],
            'org_net_amount'      => ['sometimes', 'numeric'],
            'grp_net_amount'      => ['sometimes', 'numeric'],
            'created_at'          => ['sometimes', 'required', 'date'],
            'tax_category_id'     => ['sometimes', 'required', 'exists:tax_categories,id'],
            'date'                => ['sometimes', 'required', 'date'],
            'submitted_at'        => ['sometimes', 'required', 'date'],
            'fetched_at'          => ['sometimes', 'required', 'date'],
        ];

        // when importing from other system
        if (!$this->strict) {
            $rules['in_warehouse_at'] = ['sometimes', 'required', 'date'];
        }


        return $rules;
    }

    public function action(Order $order, HistoricAsset $historicAsset, array $modelData, bool $strict = true): Transaction
    {
        $this->initialisationFromShop($order->shop, $modelData);
        $this->strict = $strict;

        return $this->handle($order, $historicAsset, $this->validatedData);
    }

    public function asController(Order $order, HistoricAsset $historicAsset, ActionRequest $request): void
    {
        $this->initialisationFromShop($order->shop, $request);
        $this->handle($order, $historicAsset, $this->validatedData);
    }
}
