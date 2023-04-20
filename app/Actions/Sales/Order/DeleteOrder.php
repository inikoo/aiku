<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Nov 2021 18:57:42 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Sales\Order;

use App\Actions\WithActionUpdate;
use App\Models\Central\Tenant;
use App\Models\Sales\Order;
use Illuminate\Console\Command;

class DeleteOrder
{
    use WithActionUpdate;

    public string $commandSignature = 'cancel:order {tenant} {id}';

    public function handle(Order $order, array $deletedData = []): Order
    {
        $order->delete();

        $order = $this->update($order, $deletedData, ['data']);
        $order->transactions()->delete();


        return $order;
    }

    public function asCommand(Command $command): int
    {
        Tenant::where('code', $command->argument('tenant'))->first()->makeCurrent();
        $this->handle(Order::findOrFail($command->argument('id')));

        return 0;
    }
}
