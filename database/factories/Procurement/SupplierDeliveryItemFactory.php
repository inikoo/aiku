<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 10 May 2023 14:06:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace Database\Factories\Procurement;

use App\Models\Assets\Currency;
use App\Models\Procurement\SupplierProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Procurement\Supplier>
 */
class SupplierDeliveryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $supplierProduct = SupplierProduct::latest()->first();

        return [
            'supplier_product_id' => $supplierProduct->id,
            'unit_price'          => fake()->numberBetween(100, 10000),
            'unit_quantity'       => fake()->numberBetween(1, 100)
        ];
    }
}
