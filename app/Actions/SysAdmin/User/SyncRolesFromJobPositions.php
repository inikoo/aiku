<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 07 May 2024 10:16:58 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\SysAdmin\User\Hydrators\UserHydrateAuthorisedModels;
use App\Enums\HumanResources\JobPosition\JobPositionScopeEnum;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncRolesFromJobPositions
{
    use AsAction;

    public function handle(User $user): void
    {
        $roles = [];

        /** @var Employee|Guest $parent */
        $parent = $user->parent;

        foreach ($parent->jobPositions as $jobPosition) {
            $jobPosition->refresh();
            if ($jobPosition->scope == JobPositionScopeEnum::ORGANISATION) {
                $roles = array_merge($roles, $jobPosition->roles()->pluck('id')->all());
            } else {
                $roles = array_merge(
                    $roles,
                    $this->getRoles($jobPosition)
                );
            }
        }


        $user->syncRoles($roles);
        UserHydrateAuthorisedModels::run($user);


        $user->refresh();
    }

    private function getRoles(JobPosition $jobPosition): array
    {
        $roles = [];
        $jobPosition->refresh();
        foreach ($jobPosition->roles as $role) {
            if (in_array($role->scope_id, $jobPosition->pivot->scopes[$role->scope_type])) {
                $roles[] = $role->id;
            }
            return $roles;
        }

        return $roles;
    }
}
