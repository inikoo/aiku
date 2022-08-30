<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 13:05:43 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Inventory\Location;

use App\Actions\UpdateModelAction;
use App\Models\Utils\ActionResult;
use App\Models\Inventory\Location;

use Lorisleiva\Actions\Concerns\AsAction;


class UpdateLocation extends UpdateModelAction
{
    use AsAction;

    public function handle(Location $location, array $modelData): ActionResult
    {
        $this->model=$location;
        $this->modelData=$modelData;
        return $this->updateAndFinalise(jsonFields:['data']);
    }

}
