<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 14:41:22 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier;

use App\Actions\HydrateModel;
use App\Actions\Procurement\Supplier\Hydrators\SupplierHydrateSupplierProducts;
use App\Models\Procurement\Supplier;
use Illuminate\Support\Collection;

class HydrateSupplier extends HydrateModel
{
    public string $commandSignature = 'hydrate:supplier {organisations?*} {--i|id=} ';

    public function handle(Supplier $supplier): void
    {
        SupplierHydrateSupplierProducts::run($supplier);
    }


    protected function getModel(string $slug): Supplier
    {
        return Supplier::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Supplier::withTrashed()->get();
    }
}
