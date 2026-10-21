<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 31 Aug 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\SupplierProduct\Hydrators;

use App\Actions\Traits\Hydrators\WithHydrateCommand;
use App\Actions\Traits\Hydrators\WithLeadTimeMeasurement;
use App\Models\SupplyChain\SupplierProduct;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SupplierProductHydrateLeadTime implements ShouldBeUnique
{
    use WithHydrateCommand;
    use WithLeadTimeMeasurement;

    public string $commandSignature = 'hydrate:supplier-product-lead-time {--s|slugs=}';

    public function __construct()
    {
        $this->model = SupplierProduct::class;
    }

    public function getJobUniqueId(SupplierProduct $supplierProduct): string
    {
        return $supplierProduct->id;
    }

    public function handle(SupplierProduct $supplierProduct): void
    {
        $supplierProduct->update($this->measureLeadTime('supplier_product_id', $supplierProduct->id));
    }
}
