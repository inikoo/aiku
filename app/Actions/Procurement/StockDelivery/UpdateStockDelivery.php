<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 09 May 2023 14:50:49 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\StockDelivery;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Procurement\StockDelivery;
use Lorisleiva\Actions\ActionRequest;

class UpdateStockDelivery
{
    use WithActionUpdate;

    public function handle(StockDelivery $stockDelivery, array $modelData): StockDelivery
    {
        return $this->update($stockDelivery, $modelData, ['data']);
    }

    //    public function authorize(ActionRequest $request): bool
    //    {
    //        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    //    }

    public function rules(): array
    {
        return [
            'number'        => ['required', 'numeric', 'unique:stock_deliveries'],
            'date'          => ['required', 'date'],
            'currency_id'   => ['required', 'exists:currencies,id'],
            'exchange'      => ['required', 'numeric']
        ];
    }

    public function action(StockDelivery $stockDelivery, array $modelData): StockDelivery
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($stockDelivery, $validatedData);
    }

    public function asController(StockDelivery $stockDelivery, ActionRequest $request): StockDelivery
    {
        $request->validate();
        return $this->handle($stockDelivery, $request->all());
    }


}
