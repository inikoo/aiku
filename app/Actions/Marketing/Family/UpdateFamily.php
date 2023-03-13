<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 09:31:51 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Family;

use App\Actions\Marketing\Family\Hydrators\FamilyHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Models\Marketing\Family;

class UpdateFamily
{
    use WithActionUpdate;

    public function handle(Family $family, array $modelData): Family
    {
        $family = $this->update($family, $modelData, ['data']);
        FamilyHydrateUniversalSearch::dispatch($family);
        return $family;
    }
}
