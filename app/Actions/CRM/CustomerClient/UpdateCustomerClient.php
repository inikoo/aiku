<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 29 Jan 2022 01:05:42 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\CRM\CustomerClient;

use App\Models\CRM\CustomerClient;
use App\Models\Utils\ActionResult;
use App\Actions\WithUpdate;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCustomerClient
{
    use AsAction;
    use WithUpdate;

    public function handle(
        CustomerClient $customerClient,
        array $modelData,
    ): ActionResult {
        $res = new ActionResult();


        $customerClient->update(Arr::except($modelData, ['data']));
        $customerClient->update($this->extractJson($modelData));


        $res->changes = $customerClient->getChanges();

        $res->model    = $customerClient;
        $res->model_id = $customerClient->id;
        $res->status   = $res->changes ? 'updated' : 'unchanged';

        return $res;
    }
}
