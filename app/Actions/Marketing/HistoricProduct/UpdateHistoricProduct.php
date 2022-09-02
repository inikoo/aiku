<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 15:11:16 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\HistoricProduct;

use App\Actions\UpdateModelAction;
use App\Models\Utils\ActionResult;
use App\Models\Marketing\HistoricProduct;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHistoricProduct extends UpdateModelAction
{
    use AsAction;

    public function handle(HistoricProduct $historicProduct, array $modelData): ActionResult
    {
        $this->model=$historicProduct;
        $this->modelData=$modelData;
        return $this->updateAndFinalise();
    }
}
