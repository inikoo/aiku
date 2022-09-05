<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 18:23:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Jobs\Middleware;


class InitialiseSourceOrganisation
{

    public function handle($job, $next): void
    {

        $organisationSource=$job->getParameters()[0];
        $organisationSource->initialisation($organisationSource->organisation);

        $next($job);
    }
}
