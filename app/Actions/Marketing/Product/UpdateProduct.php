<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 15:09:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Product;

use App\Actions\UpdateModelAction;
use App\Models\Utils\ActionResult;
use App\Models\Marketing\Product;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateProduct extends UpdateModelAction
{
    use AsAction;

    public function handle(Product $product, array $modelData): ActionResult
    {
        $this->model=$product;
        $this->modelData=$modelData;
        return $this->updateAndFinalise(jsonFields:['data','settings']);

    }
}
