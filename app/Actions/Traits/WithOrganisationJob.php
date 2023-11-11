<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 01 Mar 2023 14:22:02 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use App\Models\Organisation\Organisation;

trait WithOrganisationJob
{
    public function getJobTags(Organisation $organisation): array
    {
        return ['organisation:'.$organisation->slug];
    }
}
