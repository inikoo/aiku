<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 13:12:14 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Tenant\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraWarehouse extends FetchAurora
{
    protected function parseModel(): void
    {
        $this->parsedData["warehouse"] = [
            "name" => $this->auroraModelData->{'Warehouse Name'},
            "code" => $this->auroraModelData->{'Warehouse Code'},
            "source_id" => $this->auroraModelData->{'Warehouse Key'},
            "created_at" =>
                $this->auroraModelData->{'Warehouse Valid From'} ?? null,
        ];
    }

    protected function fetchData($id): object|null
    {
        return DB::connection("aurora")
            ->table("Warehouse Dimension")
            ->where("Warehouse Key", $id)
            ->first();
    }
}
