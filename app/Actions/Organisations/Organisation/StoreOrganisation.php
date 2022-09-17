<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 17 Aug 2022 11:58:51 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Organisations\Organisation;


use App\Actions\StoreModelAction;
use App\Models\Organisations\Organisation;
use App\Models\Utils\ActionResult;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreOrganisation extends StoreModelAction
{
    use AsAction;

    public function handle(array $organisationData): ActionResult
    {
        $organisation = Organisation::create($organisationData);
        $organisation->stats()->create();
        $organisation->inventoryStats()->create();

        return $this->finalise($organisation);
    }
}
