<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 06 Dec 2023 00:32:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\SysAdmin\Authorisation;

use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;

enum RolesEnum: string
{
    case SUPER_ADMIN  = 'super-admin';
    case SYSTEM_ADMIN = 'system-admin';

    case SUPPLY_CHAIN = 'supply-chain';

    case ORG_ADMIN              = 'org-admin';
    case PROCUREMENT_CLERK      = 'procurement-clerk';
    case PROCUREMENT_SUPERVISOR = 'procurement-supervisor';

    case HUMAN_RESOURCES_CLERK      = 'human-resources-clerk';
    case HUMAN_RESOURCES_SUPERVISOR = 'human-resources-supervisor';

    case ACCOUNTING_CLERK      = 'accounting-clerk';
    case ACCOUNTING_SUPERVISOR = 'accounting-supervisor';

    public function label(): string
    {
        return match ($this) {
            RolesEnum::SUPER_ADMIN                => __('Super admin'),
            RolesEnum::SYSTEM_ADMIN               => __('System admin'),
            RolesEnum::SUPPLY_CHAIN               => __('Supply chain'),
            RolesEnum::PROCUREMENT_CLERK          => __('Procurement clerk'),
            RolesEnum::PROCUREMENT_SUPERVISOR     => __('Procurement supervisor'),
            RolesEnum::ORG_ADMIN                  => __('Organisation admin'),
            RolesEnum::HUMAN_RESOURCES_CLERK      => __('Human resources clerk'),
            RolesEnum::HUMAN_RESOURCES_SUPERVISOR => __('Human resources supervisor'),
            RolesEnum::ACCOUNTING_CLERK           => __('Accounting clerk'),
            RolesEnum::ACCOUNTING_SUPERVISOR      => __('Accounting supervisor'),
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
            RolesEnum::PROCUREMENT_CLERK => [
                OrganisationPermissionsEnum::PROCUREMENT
            ],
            RolesEnum::HUMAN_RESOURCES_CLERK => [
                OrganisationPermissionsEnum::HUMAN_RESOURCES
            ],
            RolesEnum::HUMAN_RESOURCES_SUPERVISOR => [
                OrganisationPermissionsEnum::HUMAN_RESOURCES,
                OrganisationPermissionsEnum::SUPERVISOR_HUMAN_RESOURCES
            ],
            RolesEnum::ACCOUNTING_CLERK => [
                OrganisationPermissionsEnum::ACCOUNTING,
            ],
            RolesEnum::ACCOUNTING_SUPERVISOR => [
                OrganisationPermissionsEnum::ACCOUNTING,
                OrganisationPermissionsEnum::SUPERVISOR_ACCOUNTING
            ],
            RolesEnum::PROCUREMENT_SUPERVISOR => [
                OrganisationPermissionsEnum::PROCUREMENT,
                OrganisationPermissionsEnum::SUPERVISOR_PROCUREMENT
            ],
        };
    }

    public function scope(): string
    {
        return match ($this) {
            RolesEnum::SUPER_ADMIN,
            RolesEnum::SYSTEM_ADMIN,
            RolesEnum::SUPPLY_CHAIN => 'Group',
            default                 => 'Organisation'
        };
    }

    public static function getRolesWithScope(Group|Organisation $scope): array
    {
        $rawRoleNames = array_column(
            array_filter(RolesEnum::cases(), fn ($role) => $role->scope() == class_basename($scope)),
            'value'
        );

        $rolesNames = [];
        foreach ($rawRoleNames as $rawRolesName) {
            $rolesNames[] = self::getRoleName($rawRolesName, $scope);
        }

        return $rolesNames;
    }


    public static function getRoleName(string $rawName, Group|Organisation $scope): string
    {
        return match (class_basename($scope)) {
            'Organisation' => $rawName.'-'.$scope->slug,
            default        => $rawName
        };
    }



}
