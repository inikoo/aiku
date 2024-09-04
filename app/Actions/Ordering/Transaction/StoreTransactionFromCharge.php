<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 03 Sept 2024 23:31:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Transaction;

use App\Actions\Ordering\Order\CalculateOrderTotalAmounts;
use App\Actions\Ordering\Order\Hydrators\OrderHydrateTransactions;
use App\Actions\OrgAction;
use App\Actions\Traits\WithOrderExchanges;
use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Enums\Ordering\Transaction\TransactionStatusEnum;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreTransactionFromCharge extends OrgAction
{
    use WithOrderExchanges;


    public function handle(Order $order, array $modelData): Transaction
    {
        data_set($modelData, 'tax_category_id', $order->tax_category_id, overwrite: false);

        data_set($modelData, 'asset_type', 'Charge');
        data_set($modelData, 'asset_id', Arr::get($modelData, 'charge_id'));

        data_forget($modelData, 'charge_id');


        data_set($modelData, 'shop_id', $order->shop_id);
        data_set($modelData, 'customer_id', $order->customer_id);
        data_set($modelData, 'group_id', $order->group_id);
        data_set($modelData, 'organisation_id', $order->organisation_id);
        data_set($modelData, 'date', now(), overwrite: false);

        data_set($modelData, 'state', TransactionStateEnum::CREATING, overwrite: false);
        data_set($modelData, 'status', TransactionStatusEnum::CREATING, overwrite: false);

        data_set($modelData, 'quantity_ordered', 0);


        $modelData = $this->processExchanges($modelData, $order->shop);


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
            'charge_id' => [
                'sometimes',
                'required',
                Rule::exists('charges', 'id')
                    ->where('shop_id', $this->shop->id),
            ],
            'state'            => ['sometimes', Rule::enum(TransactionStateEnum::class)],
            'status'           => ['sometimes', Rule::enum(TransactionStatusEnum::class)],
            'gross_amount'     => ['sometimes', 'numeric'],
            'net_amount'       => ['sometimes', 'numeric'],
            'org_exchange'     => ['sometimes', 'numeric'],
            'grp_exchange'     => ['sometimes', 'numeric'],
            'org_net_amount'   => ['sometimes', 'numeric'],
            'grp_net_amount'   => ['sometimes', 'numeric'],

            'tax_category_id' => ['sometimes', 'required', 'exists:tax_categories,id'],
            'date'            => ['sometimes', 'required', 'date'],
        ];

        if (!$this->strict) {
            $rules['alt_source_id'] = ['sometimes', 'string', 'max:255'];
            $rules['fetched_at']    = ['sometimes', 'required', 'date'];
            $rules['created_at']    = ['sometimes', 'required', 'date'];
        }


        return $rules;
    }

    public function action(Order $order, array $modelData, bool $strict = true): Transaction
    {
        $this->strict = $strict;
        $this->initialisationFromShop($order->shop, $modelData);

        return $this->handle($order, $this->validatedData);
    }


}
