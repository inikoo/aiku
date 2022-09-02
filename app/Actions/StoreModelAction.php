<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:40:23 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions;

use App\Models\Utils\ActionResult;

class StoreModelAction
{

    /**
     * @var \App\Models\Utils\ActionResult
     */
    private ActionResult $res;

    function __construct()
    {
        $this->res = new ActionResult();
    }

    protected function finalise($model): ActionResult
    {
        $this->res->model    = $model;
        $this->res->model_id = $model->id;
        $this->res->status   = $this->res->changes ? 'inserted' : 'unchanged';

        return $this->res;
    }

}

