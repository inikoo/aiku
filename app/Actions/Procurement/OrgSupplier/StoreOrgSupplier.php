<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 20:54:46 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplier;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrgSuppliers;
use App\Models\Procurement\OrgSupplier;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class StoreOrgSupplier extends OrgAction
{
    public function handle(Organisation $organisation, Supplier $supplier, $modelData = []): OrgSupplier
    {
        data_set($modelData, 'group_id', $organisation->group_id);
        data_set($modelData, 'organisation_id', $organisation->id);


        /** @var OrgSupplier $orgSupplier */
        $orgSupplier = $supplier->orgSuppliers()->create($modelData);
        $orgSupplier->stats()->create();

        OrganisationHydrateOrgSuppliers::dispatch($organisation);


        return $orgSupplier;
    }


    public function rules(ActionRequest $request): array
    {
        return [
            'source_id' => 'sometimes|nullable|string|max:64',
        ];
    }

    public function action(Organisation $organisation, Supplier $supplier, $modelData = [], $hydratorDelay = 0): OrgSupplier
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorDelay;
        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $supplier, $this->validatedData);
    }


}
