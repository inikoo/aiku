<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\Order;

use App\Actions\OMS\Order\Hydrators\OrderHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Models\OMS\Order;

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
            'date'   => ['required', 'date']
        ];
    }

    public function action(Order $order, array $objectData): Order
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($order, $validatedData);
    }
}
