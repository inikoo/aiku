<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 09:56:46 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent;

use App\Actions\Central\Tenant\Hydrators\TenantHydrateProcurement;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Models\Central\Tenant;
use App\Models\Procurement\Agent;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreAgent
{
    use AsAction;

    public function handle(Tenant $owner, array $modelData, array $addressData = []): Agent
    {
        /** @var Agent $agent */
        $agent = $owner->agents()->create($modelData);
        $agent->stats()->create();

        StoreAddressAttachToModel::run($agent, $addressData, ['scope' => 'contact']);
        $agent->location   = $agent->getLocation();
        $agent->save();
        TenantHydrateProcurement::dispatch(app('currentTenant'));

        return $agent;
    }
}
