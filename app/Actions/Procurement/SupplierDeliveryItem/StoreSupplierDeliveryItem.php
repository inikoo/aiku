<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Apr 2023 11:26:37 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\SupplierDeliveryItem;

use App\Models\Procurement\SupplierDelivery;
use App\Models\Procurement\SupplierDeliveryItem;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreSupplierDeliveryItem
{
    use AsAction;

    public function handle(SupplierDelivery $supplierDelivery, array $modelData): SupplierDeliveryItem
    {
        /** @var SupplierDeliveryItem $supplierDeliveryItem */
        $supplierDeliveryItem = $supplierDelivery->items()->create($modelData);

        return $supplierDeliveryItem;
    }
}
