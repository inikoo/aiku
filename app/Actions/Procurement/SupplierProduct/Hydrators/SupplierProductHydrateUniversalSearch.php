<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierProduct\Hydrators;

use App\Actions\WithRoutes;
use App\Actions\WithTenantJob;
use App\Models\Procurement\SupplierProduct;
use Lorisleiva\Actions\Concerns\AsAction;

class SupplierProductHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;
    use WithRoutes;

    public function handle(SupplierProduct $supplierProduct): void
    {
        $supplierProduct->universalSearch()->create(
            [
                'section' => 'Procurement',
                'route' => $this->routes(),
                'icon' => 'fa-box-usd',
                'primary_term'   => $supplierProduct->name,
                'secondary_term' => $supplierProduct->code
            ]
        );
    }

}
