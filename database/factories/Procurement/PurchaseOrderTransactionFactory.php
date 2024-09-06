<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 05 May 2023 09:23:52 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Factories\Procurement;

use App\Models\SupplyChain\SupplierProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderTransactionFactory extends Factory
{
    public function definition(): array
    {
        $supplierProduct = SupplierProduct::latest()->first();

        return [
            'supplier_product_id' => $supplierProduct->id,
            'unit_price'          => fake()->numberBetween(100, 10000),
            'unit_quantity'       => fake()->numberBetween(1, 100)
        ];
    }
}
