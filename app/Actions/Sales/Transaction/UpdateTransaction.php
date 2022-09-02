<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 13:34:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Transaction;

use App\Actions\UpdateModelAction;
use App\Models\Utils\ActionResult;
use App\Models\Sales\Transaction;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateTransaction extends UpdateModelAction
{
    use AsAction;

    public function handle(Transaction $transaction, array $modelData): ActionResult
    {

        $this->model=$transaction;
        $this->modelData=$modelData;
        return $this->updateAndFinalise(jsonFields:['data']);

    }
}
