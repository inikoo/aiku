<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 May 2023 15:26:45 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Organisation\Organisation;

use App\Actions\Procurement\SupplierProduct\Hydrators\SupplierProductHydrateUniversalSearch;
use App\Actions\Organisation\Organisation\Hydrators\OrganisationHydrateProcurement;
use App\Models\Procurement\SupplierProduct;
use App\Models\Organisation\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;

class AttachSupplierProduct
{
    use AsAction;

    public function handle(Organisation $organisation, SupplierProduct $supplierProduct, array $pivotData = []): Organisation
    {
        return $organisation->execute(function (Organisation $organisation) use ($supplierProduct, $pivotData) {
            $organisation->supplierProducts()->attach($supplierProduct, $pivotData);
            OrganisationHydrateProcurement::dispatch($organisation);
            SupplierProductHydrateUniversalSearch::dispatch($supplierProduct);

            return $organisation;
        });
    }

}
