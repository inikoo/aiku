<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 00:50:04 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Supplier\Hydrators;

use App\Enums\Procurement\SupplierProduct\SupplierProductQuantityStatusEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductStateEnum;
use App\Models\SupplyChain\Supplier;
use App\Models\SupplyChain\SupplierProduct;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class SupplierHydrateSupplierProducts
{
    use AsAction;

    public function handle(Supplier $supplier): void
    {
        $stats = [
            'number_supplier_products' => $supplier->products->count()
        ];

        $stateCounts = SupplierProduct::where('supplier_id', $supplier->id)
            ->selectRaw('state, count(*) as total')
            ->groupBy('state')
            ->pluck('total', 'state')->all();

        foreach (SupplierProductStateEnum::cases() as $productState) {
            $stats['number_supplier_products_state_'.$productState->snake()] = Arr::get($stateCounts, $productState->value, 0);
        }

        $stockQuantityStatusCounts = SupplierProduct::where('supplier_id', $supplier->id)
            ->selectRaw('stock_quantity_status, count(*) as total')
            ->groupBy('stock_quantity_status')
            ->pluck('total', 'stock_quantity_status')->all();

        foreach (SupplierProductQuantityStatusEnum::cases() as $stockQuantityStatus) {
            $stats['number_supplier_products_stock_quantity_status_'.$stockQuantityStatus->snake()] = Arr::get($stockQuantityStatusCounts, $stockQuantityStatus->value, 0);
        }

        $supplier->stats()->update($stats);
    }


}
