<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 20:54:46 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplier;

use App\Actions\OrgAction;
use App\Actions\Procurement\OrgAgent\Hydrators\OrgAgentHydrateOrgSuppliers;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrgSuppliers;
use App\Models\Procurement\OrgAgent;
use App\Models\Procurement\OrgSupplier;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class StoreOrgSupplier extends OrgAction
{
    public function handle(Organisation|OrgAgent $parent, Supplier $supplier, $modelData = []): OrgSupplier
    {

        if($parent instanceof OrgAgent) {
            $organisation = $parent->organisation;
        } else {
            $organisation = $parent;
        }

        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $organisation->id);
        data_set($modelData, 'status', $supplier->status);

        data_set($modelData, 'supplier_id', $supplier->id);
        data_set($modelData, 'agent_id', $supplier->agent_id);


        /** @var OrgSupplier $orgSupplier */
        $orgSupplier = $parent->orgSuppliers()->create($modelData);
        $orgSupplier->stats()->create();

        OrganisationHydrateOrgSuppliers::dispatch($organisation);
        if($orgSupplier->org_agent_id) {
            OrgAgentHydrateOrgSuppliers::dispatch($orgSupplier->orgAgent);
        }

        return $orgSupplier;
    }


    public function rules(ActionRequest $request): array
    {
        return [
            'source_id' => 'sometimes|nullable|string|max:64',
        ];
    }

    public function action(Organisation|OrgAgent $parent, Supplier $supplier, $modelData = [], $hydratorDelay = 0): OrgSupplier
    {
        if($parent instanceof OrgAgent) {
            $organisation = $parent->organisation;
        } else {
            $organisation = $parent;
        }

        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorDelay;
        $this->initialisation($organisation, $modelData);

        return $this->handle($parent, $supplier, $this->validatedData);
    }


}
