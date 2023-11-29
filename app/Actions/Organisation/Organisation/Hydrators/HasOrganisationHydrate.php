<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Organisation\Organisation\Hydrators;

use App\Models\Organisation\Organisation;

trait HasOrganisationHydrate
{
    public function getJobUniqueId(Organisation $organisation): string
    {
        return $organisation->id;
    }


}
