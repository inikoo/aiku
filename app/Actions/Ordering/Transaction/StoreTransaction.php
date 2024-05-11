<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:32 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Transaction;

use App\Actions\OrgAction;
use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Enums\Ordering\Transaction\TransactionStatusEnum;
use App\Enums\Ordering\Transaction\TransactionTypeEnum;
use App\Models\Catalogue\HistoricOuterable;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreTransaction extends OrgAction
{
    use WithAttributes;

    public function handle(Order $order, HistoricOuterable $item, array $modelData): Transaction
    {
        data_set($modelData, 'shop_id', $order->shop_id);
        data_set($modelData, 'customer_id', $order->customer_id);
        data_set($modelData, 'group_id', $order->group_id);
        data_set($modelData, 'organisation_id', $order->organisation_id);

        data_set($modelData, 'item_type', class_basename($item));
        data_set($modelData, 'item_id', $item->id);


        /** @var Transaction $transaction */
        $transaction = $order->transactions()->create($modelData);

        return $transaction;
    }

    public function rules(): array
    {
        return [
            'date'                => ['required', 'date'],
            'type'                => ['required', Rule::enum(TransactionTypeEnum::class)],
            'quantity_bonus'      => ['required', 'numeric', 'min:0'],
            'quantity_ordered'    => ['required', 'numeric', 'min:0'],
            'quantity_dispatched' => ['required', 'numeric', 'min:0'],
            'quantity_fail'       => ['required', 'numeric', 'min:0'],
            'quantity_cancelled'  => ['sometimes', 'numeric', 'min:0'],

            'source_id'        => ['sometimes', 'string'],
            'state'            => ['sometimes', Rule::enum(TransactionStateEnum::class)],
            'status'           => ['sometimes', Rule::enum(TransactionStatusEnum::class)],
            'org_exchange'     => ['sometimes', 'numeric'],
            'group_exchange'   => ['sometimes', 'numeric'],
            'org_net_amount'   => ['sometimes', 'numeric'],
            'group_net_amount' => ['sometimes', 'numeric'],
            'tax_rate'         => ['required', 'numeric', 'min:0'],
            'created_at'       => ['sometimes', 'required', 'date'],


        ];
    }

    public function action(Order $order, $item, array $modelData): Transaction
    {
        $this->initialisationFromShop($order->shop, $modelData);

        return $this->handle($order, $item, $this->validatedData);
    }
}
