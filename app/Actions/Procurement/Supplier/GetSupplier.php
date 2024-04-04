<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 03 May 2023 13:26:09 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier;

use App\Actions\SupplyChain\Supplier\UI\IndexSuppliers;
use App\Models\SupplyChain\Agent;
use Lorisleiva\Actions\Concerns\AsAction;

class GetSupplier
{
    use AsAction;


    public function handle(Agent $agent)
    {
        return IndexSuppliers::run($agent);


    }
}
