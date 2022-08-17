<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 17 Aug 2022 11:58:51 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Organisations\Organisation;


use App\Models\Organisations\Organisation;
use App\Models\Utils\ActionResult;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreOrganisation
{
    use AsAction;

    public function handle(array $organisationData): ActionResult
    {
        $res = new ActionResult();


        $organisation = Organisation::create($organisationData);


        $res->model    = $organisation;
        $res->model_id = $organisation->id;
        $res->status   = $res->model_id ? 'inserted' : 'error';

        return $res;
    }
}
