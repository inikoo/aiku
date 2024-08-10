<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 07 May 2024 10:16:58 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\User;

use App\Actions\SysAdmin\User\Hydrators\UserHydrateAuthorisedModels;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\HumanResources\JobPosition\JobPositionScopeEnum;
use App\Enums\SysAdmin\Authorisation\RolesEnum;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\Role;
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
            if ($jobPosition->scope == JobPositionScopeEnum::ORGANISATION || $jobPosition->scope == JobPositionScopeEnum::GROUP) {
                $roles = array_merge($roles, $jobPosition->roles()->pluck('id')->all());
            } else {
                $roles = array_merge(
                    $roles,
                    $this->getRoles($jobPosition)
                );
            }
        }



        $user->syncRoles($roles);

        if ($user->roles()->where('name', RolesEnum::GROUP_ADMIN->value)->exists()) {

            foreach ($user->group->organisations as $organisation) {
                UserAddRoles::run($user, [
                    Role::where('name', RolesEnum::getRoleName(RolesEnum::ORG_ADMIN->value, $organisation))->first()
                ]);
            }
            foreach($user->group->shops  as $shop) {
                if ($shop->type == ShopTypeEnum::FULFILMENT) {
                    UserAddRoles::run($user, [
                        Role::where('name', RolesEnum::getRoleName(RolesEnum::FULFILMENT_WAREHOUSE_SUPERVISOR->value, $shop->fulfilment))->first()
                    ]);
                    UserAddRoles::run($user, [
                        Role::where('name', RolesEnum::getRoleName(RolesEnum::FULFILMENT_SHOP_SUPERVISOR->value, $shop->fulfilment))->first()
                    ]);
                } else {
                    UserAddRoles::run($user, [
                        Role::where('name', RolesEnum::getRoleName(RolesEnum::SHOP_ADMIN->value, $shop))->first()
                    ]);
                }
            }
            foreach ($user->group->warehouses as $warehouse) {
                UserAddRoles::run($user, [
                    Role::where('name', RolesEnum::getRoleName(RolesEnum::WAREHOUSE_ADMIN->value, $warehouse))->first()
                ]);
            }

            foreach ($user->group->productions as $production) {
                UserAddRoles::run($user, [
                    Role::where('name', RolesEnum::getRoleName(RolesEnum::MANUFACTURING_ADMIN->value, $production))->first()
                ]);
            }

        }


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
