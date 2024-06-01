<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 18:29:16 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Billable;

use App\Actions\HydrateModel;
use App\Actions\Catalogue\Billable\Hydrators\ProductHydrateUniversalSearch;
use App\Models\Catalogue\Billable;
use Illuminate\Support\Collection;

class UpdateProductUniversalSearch extends HydrateModel
{
    public string $commandSignature = 'product:search {organisations?*} {--s|slugs=}';


    public function handle(Billable $product): void
    {
        ProductHydrateUniversalSearch::run($product);
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
