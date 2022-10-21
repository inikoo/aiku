<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 21 Oct 2022 08:31:09 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Marketing\Department;

use App\Actions\WithActionUpdate;
use App\Models\Marketing\Department;

class UpdateDepartment
{
    use WithActionUpdate;

    public function handle(Department $department, array $modelData): Department
    {
        return $this->update($department, $modelData, ['data']);
    }
}
