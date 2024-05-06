<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 30 Sept 2022 10:28:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Traits;

use App\Models\HumanResources\JobPosition;
use Spatie\Permission\Traits\HasRoles as SpatieHasRoles;

trait HasRoles
{
    use SpatieHasRoles;

    public function assignJoBPositionRoles(JobPosition $jobPosition): void
    {
        foreach ($jobPosition->roles as $roleID) {
            $this->assignRole($roleID);
            $this->roles()->updateExistingPivot($roleID, ['locked' => true]);
        }
    }

    public function removeJoBPositionRoles(JobPosition $jobPosition): void
    {
        foreach ($jobPosition->roles as $roleID) {
            $currentRole=$this->roles()->wherePivot('role_id', $roleID)->first();

            if ($currentRole && $currentRole->pivot->direct_role) {
                $this->roles()->updateExistingPivot($roleID, ['locked' => false]);
            } else {
                $this->removeRole($roleID);
            }
        }
    }



}
