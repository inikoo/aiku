<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 30 Sept 2022 10:28:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Traits;


use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles as SpatieHasRoles;


trait HasRoles
{
    use SpatieHasRoles;

    public function assignJoBPositionRoles(JobPosition $jobPosition): void
    {
        foreach ($jobPosition->roles as $roleID) {
            $this->assignRole($roleID);
            $this->roles()->updateExistingPivot($roleID, ['job_position_role' => true]);
        }
    }

    public function removeJoBPositionRoles(JobPosition $jobPosition): void
    {
        foreach ($jobPosition->roles as $roleID) {

            $currentRole=$this->roles()->wherePivot('role_id', $roleID)->first();

            if ($currentRole && $currentRole->pivot->direct_role) {
                $this->roles()->updateExistingPivot($roleID, ['job_position_role' => false]);
            } else {
                print  "xxxx $roleID xxxx";
                $this->removeRole($roleID);
            }
        }
    }

    function assignDirectRole(Role $role): void
    {
        $this->assignRole($role);
        $this->roles()->updateExistingPivot($role->id, ['direct_role' => true]);
    }

    function removeDirectRole(Role $role): void
    {
        $currentRole=$this->roles()->wherePivot('role_id', $role->id)->first();

        if ($currentRole &&  $currentRole->pivot->job_position_role) {

            $this->roles()->updateExistingPivot($role->id, ['direct_role' => false]);
        } else {
            $this->removeRole($role->id);
        }

    }

    public function roles(): BelongsToMany
    {
        $relation = $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            PermissionRegistrar::$pivotRole
        )->withPivot(['direct_role', 'job_position_role']);

        if (!PermissionRegistrar::$teams) {
            return $relation;
        }

        return $relation->wherePivot(PermissionRegistrar::$teamsKey, getPermissionsTeamId())
            ->where(function ($q) {
                $teamField = config('permission.table_names.roles').'.'.PermissionRegistrar::$teamsKey;
                $q->whereNull($teamField)->orWhere($teamField, getPermissionsTeamId());
            });
    }

}


