<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\Order;

use App\Actions\Traits\WithActionUpdate;
use App\Enums\OMS\Order\OrderStateEnum;
use App\Models\OMS\Order;
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
