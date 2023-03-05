<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 22:01:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Employee;

use App\Models\HumanResources\Employee;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreEmployee
{
    use AsAction;

    public function handle(array $modelData): Employee
    {
        return Employee::create($modelData);
    }
}
