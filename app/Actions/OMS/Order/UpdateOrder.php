<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\Order;

use App\Actions\OMS\Order\Hydrators\OrderHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\OMS\Order;
use App\Rules\IUnique;

class UpdateOrder extends OrgAction
{
    use WithActionUpdate;

    private Order $order;

    public function handle(Order $order, array $modelData): Order
    {
        $order =  $this->update($order, $modelData, ['data']);

        OrderHydrateUniversalSearch::dispatch($order);
        return $order;
    }

    public function rules(): array
    {
        $rules= [
            'number'           => [
                'sometimes',
                'string',
                'max:64',
                new IUnique(
                    table: 'orders',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'id', 'value' => $this->order->id, 'operator' => '!=']
                    ]
                ),
            ],
            'date'   => ['sometimes','required', 'date']
        ];

        if(!$this->strict) {

            $rules['number']= ['sometimes', 'string', 'max:64'];
        }

        return $rules;

    }

    public function action(Order $order, array $modelData, bool $strict=true, int $hydratorsDelay = 0): Order
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->order          = $order;

        $this->initialisationFromShop($order->shop, $modelData);

        return $this->handle($order, $this->validatedData);
    }
}
