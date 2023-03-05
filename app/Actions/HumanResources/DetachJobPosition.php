<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 30 Sept 2022 12:24:58 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources;

use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Guest;
use Lorisleiva\Actions\Concerns\AsAction;

class DetachJobPosition
{
    use AsAction;


    public function handle(Employee|Guest $model, JobPosition $jobPosition): void
    {
        $model->jobPositions()->detach($jobPosition->id);
        $model->user?->removeJoBPositionRoles($jobPosition);
    }
}
