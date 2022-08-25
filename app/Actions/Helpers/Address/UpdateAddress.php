<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 27 Sep 2021 18:41:06 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Helpers\Address;

use App\Models\Utils\ActionResult;
use App\Models\Helpers\Address;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateAddress
{
    use AsAction;

    public function handle(Address $address,array $data): ActionResult
    {
        $res = new ActionResult();
        $address->update($data);
        $res->changes = array_merge($res->changes, $address->getChanges());

        $res->model    = $address;
        $res->model_id = $address->id;
        $res->status   = $res->changes ? 'updated' : 'unchanged';


        return $res;
    }
}
