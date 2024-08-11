<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 04 Apr 2024 09:14:15 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgSupplier;

use App\Actions\OrgAction;
use App\Models\SupplyChain\Supplier;

class StoreOrgSupplierFromSupplierInAgent extends OrgAction
{
    public function handle(Supplier $supplier, array $modelData = []): void
    {
        if (!$supplier->agent_id) {
            return;
        }


        foreach ($supplier->agent->orgAgents as $orgAgent) {
            StoreOrgSupplier::make()->action(
                $orgAgent,
                $supplier,
                $modelData
            );
        }
    }
}
