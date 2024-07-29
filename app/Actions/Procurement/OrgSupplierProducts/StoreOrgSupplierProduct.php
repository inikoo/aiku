<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 May 2024 20:51:57 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplierProducts;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrgSupplierProducts;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use Lorisleiva\Actions\ActionRequest;

class StoreOrgSupplierProduct extends OrgAction
{
    public function handle(OrgSupplier $orgSupplier, SupplierProduct $supplierProduct, $modelData = []): OrgSupplierProduct
    {
        data_set($modelData, 'group_id', $orgSupplier->group_id);
        data_set($modelData, 'organisation_id', $orgSupplier->organisation_id);


        data_set($modelData, 'org_agent_id', $orgSupplier->org_agent_id);
        data_set($modelData, 'org_supplier_id', $orgSupplier->id);

        //

        /** @var OrgSupplierProduct $orgSupplierProduct */
        $orgSupplierProduct = $supplierProduct->orgSupplierProducts()->create($modelData);
        $orgSupplierProduct->stats()->create();

        OrganisationHydrateOrgSupplierProducts::dispatch($orgSupplier->organisation);


        return $orgSupplierProduct;
    }


    public function rules(ActionRequest $request): array
    {
        return [
            'source_id' => 'sometimes|nullable|string|max:64',
        ];
    }

    public function action(OrgSupplier $orgSupplier, SupplierProduct $supplierProduct, $modelData = [], $hydratorDelay = 0): OrgSupplierProduct
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorDelay;
        $this->initialisation($orgSupplier->organisation, $modelData);

        return $this->handle($orgSupplier, $supplierProduct, $this->validatedData);
    }


}
