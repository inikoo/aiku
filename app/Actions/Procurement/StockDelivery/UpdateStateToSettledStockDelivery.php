<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 10 May 2023 14:06:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\StockDelivery;

use App\Actions\Procurement\StockDelivery\Traits\HasStockDeliveryHydrators;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Procurement\StockDelivery\StockDeliveryStateEnum;
use App\Models\Procurement\StockDelivery;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateStateToSettledStockDelivery
{
    use WithActionUpdate;
    use AsAction;
    use HasStockDeliveryHydrators;

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(StockDelivery $stockDelivery): StockDelivery
    {
        $data = [
            'state' => StockDeliveryStateEnum::SETTLED,
        ];

        if ($stockDelivery->state === StockDeliveryStateEnum::CHECKED) {
            $data[$stockDelivery->state->value . '_at']    = null;
            $data['settled_at']                            = now();

            $stockDelivery = $this->update($stockDelivery, $data);

            $this->runHydrators($stockDelivery);

            return $stockDelivery;
        }

        throw ValidationException::withMessages(['status' => 'You can not change the status to settled']);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function action(StockDelivery $stockDelivery): StockDelivery
    {
        return $this->handle($stockDelivery);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function asController(StockDelivery $stockDelivery): StockDelivery
    {
        return $this->handle($stockDelivery);
    }
}
