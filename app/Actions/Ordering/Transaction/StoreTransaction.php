<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:32 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Transaction;

use App\Actions\Ordering\Order\Hydrators\OrderHydrateTransactions;
use App\Actions\OrgAction;
use App\Actions\Traits\WithOrderExchanges;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Enums\Ordering\Transaction\TransactionStatusEnum;
use App\Enums\Ordering\Transaction\TransactionTypeEnum;
use App\Models\Catalogue\HistoricAsset;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use Illuminate\Validation\Rule;

class StoreTransaction extends OrgAction
{
    use WithOrderExchanges;

    public function handle(Order $order, HistoricAsset $historicAsset, array $modelData): Transaction
    {
        data_set($modelData, 'shop_id', $order->shop_id);
        data_set($modelData, 'customer_id', $order->customer_id);
        data_set($modelData, 'group_id', $order->group_id);
        data_set($modelData, 'organisation_id', $order->organisation_id);
        data_set($modelData, 'historic_asset_id', $historicAsset->id);
        data_set($modelData, 'asset_id', $historicAsset->asset_id);

        data_set($modelData, 'date', now(), overwrite: false);
        data_set($modelData, 'submitted_at', $order->submitted_at, overwrite: false);

        $modelData = $this->processExchanges($modelData, $order->shop);

        $assetType = match ($historicAsset->model_type) {
            'Service' => AssetTypeEnum::SERVICE,
            default   => AssetTypeEnum::PRODUCT,
        };


        data_set($modelData, 'asset_type', $assetType);


        /** @var Transaction $transaction */
        $transaction = $order->transactions()->create($modelData);

        OrderHydrateTransactions::dispatch($order);

        return $transaction;
    }

    public function rules(): array
    {
        $rules= [
            'type'                => ['required', Rule::enum(TransactionTypeEnum::class)],
            'quantity_ordered'    => ['required', 'numeric', 'min:0'],
            'quantity_bonus'      => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_dispatched' => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_fail'       => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_cancelled'  => ['sometimes', 'sometimes', 'numeric', 'min:0'],
            'source_id'           => ['sometimes', 'string'],
            'state'               => ['sometimes', Rule::enum(TransactionStateEnum::class)],
            'status'              => ['sometimes', Rule::enum(TransactionStatusEnum::class)],
            'gross_amount'        => ['required', 'numeric'],
            'net_amount'          => ['required', 'numeric'],
            'org_exchange'        => ['sometimes', 'numeric'],
            'grp_exchange'        => ['sometimes', 'numeric'],
            'org_net_amount'      => ['sometimes', 'numeric'],
            'grp_net_amount'      => ['sometimes', 'numeric'],
            'created_at'          => ['sometimes', 'required', 'date'],
            'tax_category_id'     => ['required', 'exists:tax_categories,id'],

            'date'         => ['sometimes', 'required', 'date'],
            'submitted_at' => ['sometimes', 'required', 'date'],
        ];

        // when importing from other system
        if(!$this->strict) {
            $rules['in_warehouse_at']= ['sometimes', 'required', 'date'];
        }


        return $rules;
    }

    public function action(Order $order, HistoricAsset $historicAsset, array $modelData, bool $strict=true): Transaction
    {
        $this->initialisationFromShop($order->shop, $modelData);
        $this->strict = $strict;

        return $this->handle($order, $historicAsset, $this->validatedData);
    }
}
