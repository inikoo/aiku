<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:32 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Transaction;

use App\Actions\Ordering\Order\Hydrators\OrderHydrateTransactions;
use App\Actions\OrgAction;
use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Enums\Ordering\Transaction\TransactionStatusEnum;
use App\Enums\Ordering\Transaction\TransactionTypeEnum;
use App\Models\Catalogue\HistoricAsset;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreTransaction extends OrgAction
{
    use WithAttributes;

    public function handle(Order $order, HistoricAsset $historicAsset, array $modelData): Transaction
    {
        data_set($modelData, 'shop_id', $order->shop_id);
        data_set($modelData, 'customer_id', $order->customer_id);
        data_set($modelData, 'group_id', $order->group_id);
        data_set($modelData, 'organisation_id', $order->organisation_id);
        data_set($modelData, 'historic_asset_id', $historicAsset->id);
        data_set($modelData, 'asset_id', $historicAsset->asset_id);
        data_set($modelData, 'date', now(), overwrite: false);

        $assetType=match ($historicAsset->model_type) {
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
        return [
            'date'                => ['sometimes', 'required', 'date'],
            'type'                => ['required', Rule::enum(TransactionTypeEnum::class)],
            'quantity_ordered'    => ['required', 'numeric', 'min:0'],
            'quantity_bonus'      => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_dispatched' => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_fail'       => ['sometimes', 'required', 'numeric', 'min:0'],
            'quantity_cancelled'  => ['sometimes', 'sometimes', 'numeric', 'min:0'],

            'source_id'        => ['sometimes', 'string'],
            'state'            => ['sometimes', Rule::enum(TransactionStateEnum::class)],
            'status'           => ['sometimes', Rule::enum(TransactionStatusEnum::class)],
            'org_exchange'     => ['sometimes', 'numeric'],
            'group_exchange'   => ['sometimes', 'numeric'],
            'org_net_amount'   => ['sometimes', 'numeric'],
            'group_net_amount' => ['sometimes', 'numeric'],
            'tax_rate'         => ['sometimes', 'required', 'numeric', 'min:0'],
            'created_at'       => ['sometimes', 'required', 'date'],


        ];
    }

    public function action(Order $order, HistoricAsset $historicAsset, array $modelData): Transaction
    {
        $this->initialisationFromShop($order->shop, $modelData);

        return $this->handle($order, $historicAsset, $this->validatedData);
    }
}
