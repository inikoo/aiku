<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 04 Sept 2024 15:22:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Transaction;

use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Enums\Ordering\Transaction\TransactionStatusEnum;
use App\Models\Ordering\Order;

trait WithNoProductStoreTransaction
{
    private function transactionFieldProcess(Order $order, $modelData): array
    {
        data_set($modelData, 'tax_category_id', $order->tax_category_id, overwrite: false);

        data_set($modelData, 'shop_id', $order->shop_id);
        data_set($modelData, 'customer_id', $order->customer_id);
        data_set($modelData, 'group_id', $order->group_id);
        data_set($modelData, 'organisation_id', $order->organisation_id);
        data_set($modelData, 'date', now(), overwrite: false);
        data_set($modelData, 'state', TransactionStateEnum::CREATING, overwrite: false);
        data_set($modelData, 'status', TransactionStatusEnum::CREATING, overwrite: false);
        data_set($modelData, 'quantity_ordered', 0);

        return $this->processExchanges($modelData, $order->shop);
    }
}
