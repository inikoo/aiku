<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Feb 2023 13:09:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentOrder;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Fulfilment\FulfilmentOrder;
use App\Models\Grouping\Organisation;
use Illuminate\Console\Command;

class DeleteFulfilmentOrder
{
    use WithActionUpdate;

    public string $commandSignature = 'cancel:fulfilment-order {tenant} {id}';

    public function handle(FulfilmentOrder $fulfilmentOrder, array $deletedData = []): FulfilmentOrder
    {
        $fulfilmentOrder->delete();

        $fulfilmentOrder = $this->update($fulfilmentOrder, $deletedData, ['data']);
        $fulfilmentOrder->items()->delete();


        return $fulfilmentOrder;
    }

    public function asCommand(Command $command): int
    {
        Organisation::where('slug', $command->argument('tenant'))->first()->makeCurrent();
        $this->handle(FulfilmentOrder::findOrFail($command->argument('id')));

        return 0;
    }
}
