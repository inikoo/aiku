<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 02:04:48 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Marketing\Shop;

use App\Models\Utils\ActionResult;
use App\Actions\WithUpdate;
use App\Models\Marketing\Shop;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateShop
{
    use AsAction;
    use WithUpdate;

    public function handle(Shop $shop, array $modelData): ActionResult
    {
        $res = new ActionResult();

        $shop->update(Arr::except($modelData, ['data', 'settings']));
        $shop->update($this->extractJson($modelData, ['data', 'settings']));

        $res->changes = array_merge($res->changes, $shop->getChanges());

        $res->model    = $shop;
        $res->model_id = $shop->id;
        $res->status   = $res->changes ? 'updated' : 'unchanged';

        return $res;
    }
}
