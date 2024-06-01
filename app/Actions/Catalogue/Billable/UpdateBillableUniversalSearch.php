<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 18:29:16 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Billable;

use App\Actions\HydrateModel;
use App\Actions\Catalogue\Billable\Hydrators\BillableHydrateUniversalSearch;
use App\Models\Catalogue\Billable;
use Illuminate\Support\Collection;

class UpdateBillableUniversalSearch extends HydrateModel
{
    public string $commandSignature = 'billable:search {organisations?*} {--s|slugs=}';


    public function handle(Billable $product): void
    {
        BillableHydrateUniversalSearch::run($product);
    }


    protected function getModel(string $slug): Billable
    {
        return Billable::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Billable::get();
    }
}
