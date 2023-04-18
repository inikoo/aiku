<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 10:53:52 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier;

use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Central\Tenant\Hydrators\TenantHydrateProcurement;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Procurement\Agent\Hydrators\AgentHydrateSuppliers;
use App\Actions\Procurement\Supplier\Hydrators\SupplierHydrateUniversalSearch;
use App\Models\Central\Tenant;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreSupplier
{
    use AsAction;

    public function handle(Tenant|Agent $owner, array $modelData, array $addressData = []): Supplier
    {
        if (class_basename($owner) == 'Agent') {
            $modelData['owner_type'] = $owner->owner_type;
            $modelData['owner_id']   = $owner->owner_id;
        }

        /** @var Supplier $supplier */
        $supplier = $owner->suppliers()->create($modelData);

        $supplier->stats()->create();
        SetCurrencyHistoricFields::run($supplier->currency, $supplier->created_at);


        StoreAddressAttachToModel::run($supplier, $addressData, ['scope' => 'contact']);

        $supplier->location = $supplier->getLocation();
        $supplier->save();

        TenantHydrateProcurement::dispatch(app('currentTenant'));
        if ($supplier->agent_id) {
            AgentHydrateSuppliers::dispatch($supplier->agent);
        }

        SupplierHydrateUniversalSearch::dispatch($supplier);

        return $supplier;
    }
}
