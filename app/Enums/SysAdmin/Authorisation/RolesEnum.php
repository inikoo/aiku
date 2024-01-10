<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 06 Dec 2023 00:32:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\SysAdmin\Authorisation;

use App\Models\Inventory\Warehouse;
use App\Models\Market\Shop;
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


    case SHOP_ADMIN = 'shop-admin';

    case WAREHOUSE_ADMIN = 'warehouse-admin';

    case STOCK_CONTROLLER       = 'stock-controller';
    case CUSTOMER_SERVICE_CLERK = 'customer-service-clerk';

    case CUSTOMER_SERVICE_SUPERVISOR = 'customer-service-supervisor';

    public function label(): string
    {
        return match ($this) {
            RolesEnum::SUPER_ADMIN                 => __('Super admin'),
            RolesEnum::SYSTEM_ADMIN                => __('System admin'),
            RolesEnum::SUPPLY_CHAIN                => __('Supply chain'),
            RolesEnum::PROCUREMENT_CLERK           => __('Procurement clerk'),
            RolesEnum::PROCUREMENT_SUPERVISOR      => __('Procurement supervisor'),
            RolesEnum::ORG_ADMIN                   => __('Organisation admin'),
            RolesEnum::HUMAN_RESOURCES_CLERK       => __('Human resources clerk'),
            RolesEnum::HUMAN_RESOURCES_SUPERVISOR  => __('Human resources supervisor'),
            RolesEnum::ACCOUNTING_CLERK            => __('Accounting clerk'),
            RolesEnum::ACCOUNTING_SUPERVISOR       => __('Accounting supervisor'),
            RolesEnum::SHOP_ADMIN                  => __('Shop admin'),
            RolesEnum::WAREHOUSE_ADMIN             => __('Warehouse admin'),
            RolesEnum::CUSTOMER_SERVICE_CLERK      => __('Customer service clerk'),
            RolesEnum::CUSTOMER_SERVICE_SUPERVISOR => __('Customer service supervisor'),
        };
    }

    public function getPermissions(): array
    {
        return match ($this) {
            RolesEnum::SUPER_ADMIN => [
                GroupPermissionsEnum::GROUP_BUSINESS_INTELLIGENCE,
                GroupPermissionsEnum::SYSADMIN,
                GroupPermissionsEnum::SUPPLY_CHAIN,


            ],
            RolesEnum::SYSTEM_ADMIN => [
                GroupPermissionsEnum::SYSADMIN
            ],
            RolesEnum::SUPPLY_CHAIN => [
                GroupPermissionsEnum::SUPPLY_CHAIN
            ],
            RolesEnum::ORG_ADMIN => [
                OrganisationPermissionsEnum::ORG_BUSINESS_INTELLIGENCE,
                OrganisationPermissionsEnum::PROCUREMENT,
                OrganisationPermissionsEnum::HUMAN_RESOURCES,
                OrganisationPermissionsEnum::SUPERVISOR_HUMAN_RESOURCES,
                OrganisationPermissionsEnum::SHOPS,
                OrganisationPermissionsEnum::INVENTORIES,
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
            RolesEnum::SHOP_ADMIN => [
                ShopPermissionsEnum::PRODUCTS,
                ShopPermissionsEnum::WEB,
                ShopPermissionsEnum::CRM,
                ShopPermissionsEnum::SUPERVISOR_CRM,
                ShopPermissionsEnum::SUPERVISOR_PRODUCTS
            ],
            RolesEnum::CUSTOMER_SERVICE_CLERK => [
                ShopPermissionsEnum::CRM,
            ],
            RolesEnum::CUSTOMER_SERVICE_SUPERVISOR => [
                ShopPermissionsEnum::CRM,
                ShopPermissionsEnum::SUPERVISOR_CRM
            ],
            RolesEnum::WAREHOUSE_ADMIN => [
                WarehousePermissionsEnum::WAREHOUSE,
                WarehousePermissionsEnum::INVENTORY,
                WarehousePermissionsEnum::DISPATCHING,
                WarehousePermissionsEnum::SUPERVISOR_WAREHOUSES,
                WarehousePermissionsEnum::SUPERVISOR_INVENTORY,
                WarehousePermissionsEnum::SUPERVISOR_DISPATCHING,

            ],
            RolesEnum::STOCK_CONTROLLER => [
                WarehousePermissionsEnum::INVENTORY,
                WarehousePermissionsEnum::DISPATCHING,
            ],
        };
    }

    public function scope(): string
    {
        return match ($this) {
            RolesEnum::SUPER_ADMIN,
            RolesEnum::SYSTEM_ADMIN,
            RolesEnum::SUPPLY_CHAIN => 'Group',
            RolesEnum::SHOP_ADMIN,
            RolesEnum::CUSTOMER_SERVICE_CLERK,
            RolesEnum::CUSTOMER_SERVICE_SUPERVISOR
            => 'Shop',
            RolesEnum::WAREHOUSE_ADMIN,
            RolesEnum::STOCK_CONTROLLER => 'Warehouse',

            default => 'Organisation'
        };
    }

    public static function getRolesWithScope(Group|Organisation|Shop|Warehouse $scope): array
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


    public static function getRoleName(string $rawName, Group|Organisation|Shop|Warehouse $scope): string
    {
        return match (class_basename($scope)) {
            'Organisation', 'Shop', 'Warehouse' => $rawName.'-'.$scope->slug,
            default => $rawName
        };
    }


}
