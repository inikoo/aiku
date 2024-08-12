<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:43:03 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\WarehouseArea\Search;

use App\Actions\HydrateModel;
use App\Models\Inventory\WarehouseArea;
use Illuminate\Support\Collection;

class ReindexWarehouseAreaSearch extends HydrateModel
{
    public string $commandSignature = 'warehouse_area:search {organisations?*} {--s|slugs=}';


    public function handle(WarehouseArea $warehouseArea): void
    {
        WarehouseAreaRecordSearch::run($warehouseArea);
    }


    protected function getModel(string $slug): WarehouseArea
    {
        return WarehouseArea::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return WarehouseArea::withTrashed()->get();
    }
}
