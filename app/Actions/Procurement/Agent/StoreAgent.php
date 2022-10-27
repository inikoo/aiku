<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 09:56:46 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent;

use App\Actions\Helpers\Address\StoreAddress;
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
        if (count($addressData) > 0) {
            $addresses               = [];
            $address                 = StoreAddress::run($addressData);
            $addresses[$address->id] = ['scope' => 'default'];
            $agent->addresses()->sync($addresses);
            $agent->address_id = $address->id;
            $agent->location   = $agent->getLocation();
            $agent->save();
        }

        return $agent;
    }


}
