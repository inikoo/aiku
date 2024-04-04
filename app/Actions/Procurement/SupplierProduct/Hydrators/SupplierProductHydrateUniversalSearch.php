<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierProduct\Hydrators;

use App\Models\SupplyChain\SupplierProduct;
use Lorisleiva\Actions\Concerns\AsAction;

class SupplierProductHydrateUniversalSearch
{
    use AsAction;


    public function handle(SupplierProduct $supplierProduct): void
    {
        $supplierProduct->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'       => $supplierProduct->group_id,
                'section'        => 'procurement',
                'title'          => trim($supplierProduct->code.' '.$supplierProduct->name),
                'description'    => ''
            ]
        );
    }

}
