<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 21:37:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Supplier;

use App\Actions\OrgAction;
use App\Models\SupplyChain\Supplier;

class AttachAgentSupplierToOrganisations extends OrgAction
{
    public function handle(Supplier $supplier, array $modelData = []): void
    {
        if (!$supplier->agent_id) {
            return;
        }

        data_set($modelData, 'agent_id', $supplier->agent_id);
        foreach ($supplier->agent->organisations as $organisation) {
            $organisation->suppliers()->attach($supplier, $modelData);
        }
    }
}
