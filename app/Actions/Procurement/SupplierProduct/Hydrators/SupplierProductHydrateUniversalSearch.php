<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Procurement\SupplierProduct\Hydrators;

use App\Actions\Traits\WithOrganisationJob;
use App\Models\Procurement\SupplierProduct;
use Lorisleiva\Actions\Concerns\AsAction;

class SupplierProductHydrateUniversalSearch
{
    use AsAction;
    use WithOrganisationJob;

    public function handle(SupplierProduct $supplierProduct): void
    {
        $supplierProduct->universalSearch()->updateOrCreate(
            [],
            [
                'section'        => 'procurement',
                'title'          => trim($supplierProduct->code.' '.$supplierProduct->name),
                'description'    => ''
            ]
        );
    }

}
