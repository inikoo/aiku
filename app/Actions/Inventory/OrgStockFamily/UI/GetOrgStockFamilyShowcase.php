<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 06 Aug 2024 10:14:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStockFamily\UI;

use App\Models\Inventory\OrgStockFamily;
use Lorisleiva\Actions\Concerns\AsObject;

class GetOrgStockFamilyShowcase
{
    use AsObject;

    public function handle(OrgStockFamily $orgStockFamily): array
    {
        return [
            []
        ];
    }
}
