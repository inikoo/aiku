<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 11:01:21 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier;

use App\Actions\Procurement\Supplier\Hydrators\SupplierHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Models\Procurement\Supplier;

class UpdateSupplier
{
    use WithActionUpdate;

    public function handle(Supplier $supplier, array $modelData): Supplier
    {
        $supplier = $this->update($supplier, $modelData, ['shared_data','tenant_data','settings']);
        SupplierHydrateUniversalSearch::dispatch($supplier);
        return $supplier;
    }
}
