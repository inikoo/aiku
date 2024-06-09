<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\Traits\WithActionUpdate;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Ordering\Order;
use App\Models\SysAdmin\Organisation;
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

        throw ValidationException::withMessages(['purchase_order' => 'You can not delete this order']);
    }

    public function asCommand(Command $command): int
    {
        Organisation::where('slug', $command->argument('tenant'))->first()->makeCurrent();
        $this->handle(Order::findOrFail($command->argument('id')));

        return 0;
    }
}
