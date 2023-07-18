<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierProduct\Hydrators;

use App\Actions\Traits\WithTenantJob;
use App\Models\Procurement\SupplierProduct;
use Lorisleiva\Actions\Concerns\AsAction;

class SupplierProductHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(SupplierProduct $supplierProduct): void
    {
        $supplierProduct->universalSearch()->updateOrCreate(
            [],
            [
                'section' => 'Procurement',
                'route'   => json_encode([
                    'name'      => 'procurement.supplier-products.show',
                    'arguments' => [
                        $supplierProduct->slug
                    ]
                ]),
                'icon'           => 'fa-box-usd',
                'title'          => $supplierProduct->name,
                'description'    => $supplierProduct->code
            ]
        );
    }

}
