<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 04:43:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Leads\Prospect;

use App\Actions\Leads\Prospect\Hydrators\ProspectHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Models\Leads\Prospect;

class UpdateProspect
{
    use WithActionUpdate;

    public function handle(Prospect $prospect, array $modelData): Prospect
    {
        $prospect = $this->update($prospect, $modelData, ['data']);
        ProspectHydrateUniversalSearch::dispatch($prospect);
        return $prospect;
    }
}
