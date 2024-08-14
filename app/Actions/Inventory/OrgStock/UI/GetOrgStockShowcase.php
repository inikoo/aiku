<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Aug 2024 17:14:05 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\UI;

use App\Http\Resources\Inventory\OrgStockResource;
use App\Http\Resources\SupplyChain\AgentResource;
use App\Models\Inventory\OrgStock;
use Lorisleiva\Actions\Concerns\AsObject;

class GetOrgStockShowcase
{
    use AsObject;

    public function handle(OrgStock $orgStock): array
    {
        return [
            [
                'contactCard'              => OrgStockResource::make($orgStock)->getArray(),
            ]
        ];
    }
}
