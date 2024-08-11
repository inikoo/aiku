<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 00:50:04 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Supplier\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Procurement\SupplierProduct\SupplierProductStateEnum;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;
use Lorisleiva\Actions\Concerns\AsAction;

class SupplierHydrateSupplierProducts
{
    use AsAction;
    use WithEnumStats;

    public function handle(Supplier $supplier): void
    {
        $stats = [
            'number_supplier_products' => $supplier->products->count()
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'supplier_products',
                field: 'state',
                enum: SupplierProductStateEnum::class,
                models: SupplierProduct::class,
                where: function ($q) use ($supplier) {
                    $q->where('supplier_id', $supplier->id);
                }
            )
        );

        $supplier->stats()->update($stats);
    }


}
