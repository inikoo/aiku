<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 May 2024 20:51:57 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplierProducts;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrgSuppliers;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgSupplier;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class StoreOrgSupplierProduct extends OrgAction
{
    public function handle(Organisation $organisation, SupplierProduct $supplierProduct, $modelData = []): OrgSupplierProduct
    {
        data_set($modelData, 'group_id', $organisation->group_id);
        data_set($modelData, 'organisation_id', $organisation->id);


        if($supplierProduct->agent_id) {

            /** @var OrgAgent $orgAgent */
            $orgAgent=$organisation->orgAgents()->where('agent_id', $supplierProduct->agent_id)->first();
            data_set($modelData, 'org_agent_id', $orgAgent->id);
        }

        if($supplierProduct->supplier_id) {
            /** @var OrgSupplier $orgSupplier */
            $orgSupplier=$organisation->orgSuppliers()->where('supplier_id', $supplierProduct->supplier_id)->first();
            data_set($modelData, 'org_supplier_id', $orgSupplier->id);
        }


        /** @var OrgSupplierProduct $orgSupplierProduct */
        $orgSupplierProduct = $supplierProduct->orgSupplierProducts()->create($modelData);
        $orgSupplierProduct->stats()->create();

        OrganisationHydrateOrgSuppliers::dispatch($organisation);


        return $orgSupplierProduct;
    }


    public function rules(ActionRequest $request): array
    {
        return [
            'source_id' => 'sometimes|nullable|string|max:64',
        ];
    }

    public function action(Organisation $organisation, SupplierProduct $supplierProduct, $modelData = [], $hydratorDelay = 0): OrgSupplierProduct
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorDelay;
        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $supplierProduct, $this->validatedData);
    }


}
