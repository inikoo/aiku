<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierProduct\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Procurement\SupplierProduct;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class SupplierProductHydrateUniversalSearch implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(SupplierProduct $supplierProduct): void
    {
        $supplierProduct->universalSearch()->create(
            [
                'primary_term'   => $supplierProduct->name,
                'secondary_term' => $supplierProduct->code
            ]
        );
    }

    public function getJobUniqueId(SupplierProduct $supplierProduct): int
    {
        return $supplierProduct->id;
    }
}
