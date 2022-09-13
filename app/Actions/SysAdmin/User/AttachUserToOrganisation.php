<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Sept 2022 15:04:34 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Guest;
use App\Actions\StoreModelAction;
use App\Models\SysAdmin\User;
use App\Models\Utils\ActionResult;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;


class AttachUserToOrganisation extends StoreModelAction
{
    use AsAction;
    use WithAttributes;


    public function handle(Employee|Guest $userable, User $user): ActionResult
    {
        $modelData['userable_type'] = class_basename($userable::class);
        $modelData['userable_id']   = $userable->id;


        $userable->organisation->users()->attach(
            [
                $user->id => $modelData

            ]
        );


        return $this->finalise($user);
    }


}
