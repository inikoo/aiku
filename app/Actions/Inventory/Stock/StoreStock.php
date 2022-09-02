<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 29 Oct 2021 12:56:07 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Inventory\Stock;

use App\Actions\StoreModelAction;
use App\Models\Inventory\Stock;
use App\Models\Organisations\Organisation;
use App\Models\Utils\ActionResult;
use Lorisleiva\Actions\Concerns\AsAction;


class StoreStock extends StoreModelAction
{
    use AsAction;

    public function handle(Organisation $owner, $modelData): ActionResult
    {
        $modelData['organisation_id']=$owner->id;
        /** @var Stock $stock */
        $stock = $owner->stocks()->create($modelData);
        $stock->stats()->create();

        return $this->finalise($stock);
    }
}
