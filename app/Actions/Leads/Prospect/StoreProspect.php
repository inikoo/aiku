<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:54:17 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Leads\Prospect;

use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Leads\Prospect\Hydrators\ProspectHydrateUniversalSearch;
use App\Models\Leads\Prospect;
use App\Models\Marketing\Shop;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreProspect
{
    use AsAction;

    public function handle(Shop $shop, array $modelData, array $addressesData = []): Prospect
    {
        /** @var Prospect $prospect */
        $prospect = $shop->prospects()->create($modelData);

        StoreAddressAttachToModel::run($prospect, $addressesData, ['scope' => 'contact']);
        $prospect->location = $prospect->getLocation();
        $prospect->save();

        // TODO Create Hydrators actions
        //ShopHydrateProspects::dispatch($prospect->shop);
        //TenantHydrateProspects::dispatch(app('currentTenant'));
        ProspectHydrateUniversalSearch::dispatch($prospect);
        return $prospect;
    }
}
