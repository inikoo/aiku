<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Feb 2023 22:01:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Billable;

use App\Actions\HydrateModel;
use App\Actions\Catalogue\Billable\Hydrators\ProductInitialiseImageID;
use App\Models\Catalogue\Billable;
use Illuminate\Support\Collection;

class HydrateProduct extends HydrateModel
{
    public string $commandSignature = 'hydrate:product {organisations?*} {--i|id=} ';


    public function handle(Billable $product): void
    {
        ProductInitialiseImageID::run($product);
    }


    protected function getModel(string $slug): Billable
    {
        return Billable::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Billable::withTrashed()->get();
    }
}
