<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 06 Dec 2023 00:32:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\SysAdmin\Authorisation;

enum RolesEnum: string
{
    case SUPER_ADMIN  = 'super-admin';
    case SYSTEM_ADMIN = 'system-admin';

    case SUPPLY_CHAIN = 'supply-chain';

    case ORG_ADMIN   = 'org-admin';
    case PROCUREMENT = 'procurement';


    public function label(): string
    {
        return match ($this) {
            RolesEnum::SUPER_ADMIN  => __('Super admin'),
            RolesEnum::SYSTEM_ADMIN => __('System admin'),
            RolesEnum::SUPPLY_CHAIN => __('Supply chain'),
            RolesEnum::PROCUREMENT  => __('Procurement'),
            RolesEnum::ORG_ADMIN    => __('Organisation admin'),
        };
    }

    public function getPermissions(): array
    {
        return match ($this) {
            RolesEnum::SUPER_ADMIN => [
                GroupPermissionsEnum::GROUP_BUSINESS_INTELLIGENCE,
                GroupPermissionsEnum::SYSADMIN,
                GroupPermissionsEnum::SUPPLY_CHAIN
            ],
            RolesEnum::SYSTEM_ADMIN => [
                GroupPermissionsEnum::SYSADMIN
            ],
            RolesEnum::SUPPLY_CHAIN => [
                GroupPermissionsEnum::SUPPLY_CHAIN
            ],
            RolesEnum::ORG_ADMIN => [
                OrganisationPermissionsEnum::ORG_BUSINESS_INTELLIGENCE,
                OrganisationPermissionsEnum::PROCUREMENT
            ],
            RolesEnum::PROCUREMENT => [
                OrganisationPermissionsEnum::PROCUREMENT
            ],
        };
    }

    public function scope(): string
    {
        return match ($this) {
            RolesEnum::SUPER_ADMIN,
            RolesEnum::SYSTEM_ADMIN,
            RolesEnum::SUPPLY_CHAIN => 'group',
            default                 => 'organisation'
        };
    }



    public function isTeam(): bool
    {
        return match ($this) {
            RolesEnum::SUPER_ADMIN,
            RolesEnum::SYSTEM_ADMIN,
            RolesEnum::SUPPLY_CHAIN => false,
            default                 => true
        };
    }


    public static function getRolesWithScope(string $scope): array
    {
        return array_column(array_filter(RolesEnum::cases(), fn ($role) => $role->scope()==$scope), 'value');
    }



    public static function getAllValues(): array
    {
        return array_column(RolesEnum::cases(), 'value');
    }

    public static function getTeamValues(): array
    {
        return array_column(array_filter(RolesEnum::cases(), fn ($role) => $role->isTeam()), 'value');
    }

    public static function getNonTeamValues(): array
    {
        return array_column(array_filter(RolesEnum::cases(), fn ($role) => !$role->isTeam()), 'value');
    }

}
