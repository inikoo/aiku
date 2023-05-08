<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Nov 2021 18:57:42 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Sales\Order;

use App\Actions\WithActionUpdate;
use App\Enums\Procurement\PurchaseOrder\PurchaseOrderStateEnum;
use App\Enums\Sales\Order\OrderStateEnum;
use App\Models\Sales\Order;
use App\Models\Tenancy\Tenant;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class DeleteOrder
{
    use WithActionUpdate;

    public string $commandSignature = 'cancel:order {tenant} {id}';

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(Order $order, array $deletedData = []): Order
    {
        if (in_array($order->state, [OrderStateEnum::CREATING, OrderStateEnum::SUBMITTED])) {
            $order->delete();

            $order = $this->update($order, $deletedData, ['data']);
            $order->transactions()->delete();

            return $order;
        }

        throw ValidationException::withMessages(['purchase_order' => 'You can not delete this purchase order']);
    }

    public function asCommand(Command $command): int
    {
        Tenant::where('slug', $command->argument('tenant'))->first()->makeCurrent();
        $this->handle(Order::findOrFail($command->argument('id')));

        return 0;
    }
}
