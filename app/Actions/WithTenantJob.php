<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 01 Mar 2023 14:22:02 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions;

trait WithTenantJob
{
    public function getJobTags(): array
    {
        $tenant=app('currentTenant');
        return ['tenant:'.$tenant->code];
    }
}
