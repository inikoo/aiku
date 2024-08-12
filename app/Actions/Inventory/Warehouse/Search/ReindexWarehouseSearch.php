<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Aug 2024 21:55:06 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Warehouse\Search;

use App\Actions\HydrateModel;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Collection;

class ReindexWarehouseSearch extends HydrateModel
{
    public string $commandSignature = 'warehouse:search {organisations?*} {--s|slugs=}';


    public function handle(Warehouse $warehouse): void
    {
        WarehouseRecordSearch::run($warehouse);
    }


    protected function getModel(string $slug): Warehouse
    {
        return Warehouse::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Warehouse::withTrashed()->get();
    }
}
