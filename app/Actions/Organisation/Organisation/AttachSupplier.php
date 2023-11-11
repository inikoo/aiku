<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 May 2023 11:40:23 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Organisation\Organisation;

use App\Actions\Procurement\Supplier\Hydrators\SupplierHydrateUniversalSearch;
use App\Actions\Organisation\Organisation\Hydrators\OrganisationHydrateProcurement;
use App\Models\Procurement\Supplier;
use App\Models\Organisation\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;

class AttachSupplier
{
    use AsAction;

    public function handle(Organisation $organisation, Supplier $supplier, array $pivotData = []): Organisation
    {
        return $organisation->execute(function (Organisation $organisation) use ($supplier, $pivotData) {

            $organisation->suppliers()->attach($supplier, $pivotData);
            OrganisationHydrateProcurement::dispatch($organisation);
            SupplierHydrateUniversalSearch::dispatch($supplier);
            foreach ($supplier->products as $product) {
                AttachSupplierProduct::run($organisation, $product);
            }


            return $organisation;
        });
    }

}
