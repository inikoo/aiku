<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Nov 2021 16:23:34 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Sales\Order;

use App\Actions\Sales\Order\Hydrators\OrderHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Models\Sales\Order;

class UpdateOrder
{
    use WithActionUpdate;

    public function handle(Order $order, array $modelData): Order
    {
        $order =  $this->update($order, $modelData, ['data']);

        OrderHydrateUniversalSearch::dispatch($order);
        return $order;
    }

    public function rules(): array
    {
        return [
            'number' => ['required', 'unique:tenant.orders'],
            'date' => ['required', 'date']
        ];
    }

    public function action(Order $order, array $objectData): Order
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($order, $validatedData);
    }
}
