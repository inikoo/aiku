<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 10 May 2023 14:06:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierDeliveryItem;

use App\Actions\Procurement\SupplierDelivery\Traits\HasSupplierDeliveryHydrators;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Procurement\SupplierDeliveryItem\SupplierDeliveryItemStateEnum;
use App\Models\Procurement\SupplierDeliveryItem;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateStateToCheckedSupplierDeliveryItem
{
    use WithActionUpdate;
    use AsAction;
    use HasSupplierDeliveryHydrators;

    public function handle(SupplierDeliveryItem $supplierDeliveryItem, $modelData): SupplierDeliveryItem
    {
        $data = [
            'state' => SupplierDeliveryItemStateEnum::CHECKED,
        ];
        $data['checked_at']            = now();
        $data['unit_quantity_checked'] = $modelData['unit_quantity_checked'];

        $supplierDeliveryItem = $this->update($supplierDeliveryItem, $data);

        $this->runHydrators($supplierDeliveryItem->supplierDelivery);

        return $supplierDeliveryItem;
    }

    public function rules(): array
    {
        return [
            'unit_quantity_checked' => ['required', 'numeric']
        ];
    }

    public function action(SupplierDeliveryItem $supplierDeliveryItem, $modelData): SupplierDeliveryItem
    {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($supplierDeliveryItem, $validatedData);
    }

    public function asController(SupplierDeliveryItem $supplierDeliveryItem, ActionRequest $request): SupplierDeliveryItem
    {
        $request->validate();

        return $this->handle($supplierDeliveryItem, $request->all());
    }
}
