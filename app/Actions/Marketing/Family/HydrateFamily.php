<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 15 Feb 2022 22:39:06 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Marketing\Family;

use App\Actions\HydrateModel;
use App\Actions\Marketing\Family\Hydrators\FamilyHydrateProducts;
use App\Models\Marketing\Family;
use Illuminate\Support\Collection;

class HydrateFamily extends HydrateModel
{
    public string $commandSignature = 'hydrate:family {tenants?*} {--i|id=} ';

    public function handle(Family $family): void
    {
        FamilyHydrateProducts::run($family);
    }

    protected function getModel(int $id): Family
    {
        return Family::find($id);
    }

    protected function getAllModels(): Collection
    {
        return Family::withTrashed()->get();
    }
}
