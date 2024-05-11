<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 06 Dec 2023 00:32:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\SysAdmin\Authorisation;

use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Inventory\Warehouse;
use App\Models\Manufacturing\Production;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;

enum RolesEnum: string
{
    case SUPER_ADMIN  = 'super-admin';
    case SYSTEM_ADMIN = 'system-admin';

    case SUPPLY_CHAIN = 'supply-chain';

    case GOODS_MANAGER = 'goods-manager';

    case ORGANISATIONS_MANAGER = 'organisations-manager';


    case ORG_SHOP_ADMIN           = 'org-shop-admin';
    case ORG_DIGITAL_AGENCY_ADMIN = 'org-digital-agency-admin';
    case ORG_AGENT_ADMIN          = 'org-agent-admin';

    case PROCUREMENT_CLERK      = 'procurement-clerk';
    case PROCUREMENT_SUPERVISOR = 'procurement-supervisor';

    case DISPATCH_CLERK      = 'dispatch-clerk';
    case DISPATCH_SUPERVISOR = 'dispatch-supervisor';

    case HUMAN_RESOURCES_CLERK      = 'human-resources-clerk';
    case HUMAN_RESOURCES_SUPERVISOR = 'human-resources-supervisor';

    case ACCOUNTING_CLERK      = 'accounting-clerk';
    case ACCOUNTING_SUPERVISOR = 'accounting-supervisor';


    case SHOP_ADMIN = 'shop-admin';

    case FULFILMENT_SHOP_SUPERVISOR = 'fulfilment-shop-supervisor';
    case FULFILMENT_SHOP_CLERK      = 'fulfilment-shop-clerk';

    case FULFILMENT_WAREHOUSE_SUPERVISOR = 'fulfilment-warehouse-supervisor';
    case FULFILMENT_WAREHOUSE_WORKER     = 'fulfilment-warehouse-worker';

    case WAREHOUSE_ADMIN = 'warehouse-admin';

    case STOCK_CONTROLLER = 'stock-controller';

    case CUSTOMER_SERVICE_CLERK      = 'customer-service-clerk';
    case CUSTOMER_SERVICE_SUPERVISOR = 'customer-service-supervisor';

    case WEBMASTER_SUPERVISOR = 'webmaster-clerk-supervisor';
    case WEBMASTER_CLERK      = 'webmaster-clerk';

    case SHOPKEEPER_CLERK      = 'shopkeeper-clerk';
    case SHOPKEEPER_SUPERVISOR = 'shopkeeper-supervisor';

    case MARKETING_CLERK      = 'marketing-clerk';
    case MARKETING_SUPERVISOR = 'marketing-supervisor';


    // Digital agency roles

    case SEO_SUPERVISOR    = 'seo-supervisor';
    case SEO_CLERK         = 'seo-clerk';
    case PPC_SUPERVISOR    = 'ppc-supervisor';
    case PPC_CLERK         = 'ppc-clerk';
    case SOCIAL_SUPERVISOR = 'social-supervisor';
    case SOCIAL_CLERK      = 'social-clerk';
    case SAAS_SUPERVISOR   = 'saas-supervisor';
    case SAAS_CLERK        = 'saas-clerk';

    case MANUFACTURING_ADMIN             = 'manufacturing-admin';
    case MANUFACTURING_ORCHESTRATOR      = 'manufacturing-orchestrator';
    case MANUFACTURING_LINE_MANAGER      = 'manufacturing-line-manager';
    case MANUFACTURING_OPERATOR          = 'manufacturing-operator';
    case MANUFACTURING_PRODUCT_DEVELOPER = 'manufacturing-product-developer';


    public function label(): string
    {
        return match ($this) {
            RolesEnum::SUPER_ADMIN                     => __('Super admin'),
            RolesEnum::SYSTEM_ADMIN                    => __('System admin'),
            RolesEnum::SUPPLY_CHAIN                    => __('Supply chain'),
            RolesEnum::PROCUREMENT_CLERK               => __('Procurement clerk'),
            RolesEnum::PROCUREMENT_SUPERVISOR          => __('Procurement supervisor'),
            RolesEnum::DISPATCH_CLERK                  => __('Dispatch clerk'),
            RolesEnum::DISPATCH_SUPERVISOR             => __('Dispatch supervisor'),
            RolesEnum::ORG_SHOP_ADMIN                  => __('Organisation admin'),
            RolesEnum::ORG_DIGITAL_AGENCY_ADMIN        => __('Digital agency admin'),
            RolesEnum::HUMAN_RESOURCES_CLERK           => __('Human resources clerk'),
            RolesEnum::HUMAN_RESOURCES_SUPERVISOR      => __('Human resources supervisor'),
            RolesEnum::STOCK_CONTROLLER                => __('Stock controller'),
            RolesEnum::ACCOUNTING_CLERK                => __('Accounting clerk'),
            RolesEnum::ACCOUNTING_SUPERVISOR           => __('Accounting supervisor'),
            RolesEnum::SHOP_ADMIN                      => __('Shop admin'),
            RolesEnum::FULFILMENT_SHOP_SUPERVISOR      => __('Fulfilment supervisor'),
            RolesEnum::FULFILMENT_SHOP_CLERK           => __('Fulfilment clerk'),
            RolesEnum::FULFILMENT_WAREHOUSE_SUPERVISOR => __('Fulfilment warehouse supervisor'),
            RolesEnum::FULFILMENT_WAREHOUSE_WORKER     => __('Fulfilment warehouse worker'),
            RolesEnum::WAREHOUSE_ADMIN                 => __('Warehouse admin'),
            RolesEnum::CUSTOMER_SERVICE_CLERK          => __('Customer service clerk'),
            RolesEnum::CUSTOMER_SERVICE_SUPERVISOR     => __('Customer service supervisor'),
            RolesEnum::ORGANISATIONS_MANAGER           => __('Organisations manager'),
            RolesEnum::GOODS_MANAGER                   => __('Goods manager'),
            RolesEnum::SEO_SUPERVISOR                  => __('SEO supervisor'),
            RolesEnum::SEO_CLERK                       => __('SEO clerk'),
            RolesEnum::PPC_SUPERVISOR                  => __('PPC supervisor'),
            RolesEnum::PPC_CLERK                       => __('PPC clerk'),
            RolesEnum::SOCIAL_SUPERVISOR               => __('Social supervisor'),
            RolesEnum::SOCIAL_CLERK                    => __('Social clerk'),
            RolesEnum::SAAS_SUPERVISOR                 => __('SAAS supervisor'),
            RolesEnum::SAAS_CLERK                      => __('SAAS clerk'),
            RolesEnum::ORG_AGENT_ADMIN                 => __('Agent admin'),
            RolesEnum::WEBMASTER_CLERK                 => __('Webmaster clerk'),
            RolesEnum::WEBMASTER_SUPERVISOR            => __('Webmaster supervisor'),
            RolesEnum::SHOPKEEPER_CLERK                => __('Shopkeeper clerk'),
            RolesEnum::SHOPKEEPER_SUPERVISOR           => __('Shopkeeper supervisor'),
            RolesEnum::MARKETING_CLERK                 => __('Deals clerk'),
            RolesEnum::MARKETING_SUPERVISOR            => __('Deals supervisor'),
            RolesEnum::MANUFACTURING_ADMIN             => __('Manufacturing admin'),
            RolesEnum::MANUFACTURING_ORCHESTRATOR      => __('Manufacturing orchestrator'),
            RolesEnum::MANUFACTURING_LINE_MANAGER      => __('Manufacturing line manager'),
            RolesEnum::MANUFACTURING_OPERATOR          => __('Manufacturing operator'),
            RolesEnum::MANUFACTURING_PRODUCT_DEVELOPER => __('Manufacturing product developer'),
        };
    }

    public function getPermissions(): array
    {
        return match ($this) {
            RolesEnum::SUPER_ADMIN => [
                GroupPermissionsEnum::GROUP_REPORTS,
                GroupPermissionsEnum::GROUP_OVERVIEW,
                GroupPermissionsEnum::SYSADMIN,
                GroupPermissionsEnum::SUPPLY_CHAIN,
                GroupPermissionsEnum::ORGANISATIONS,
                GroupPermissionsEnum::GOODS
            ],
            RolesEnum::SYSTEM_ADMIN => [
                GroupPermissionsEnum::SYSADMIN
            ],
            RolesEnum::SUPPLY_CHAIN => [
                GroupPermissionsEnum::SUPPLY_CHAIN
            ],
            RolesEnum::ORGANISATIONS_MANAGER => [
                GroupPermissionsEnum::ORGANISATIONS
            ],
            RolesEnum::GOODS_MANAGER => [
                GroupPermissionsEnum::GOODS
            ],

            RolesEnum::ORG_SHOP_ADMIN => [
                OrganisationPermissionsEnum::ORG_REPORTS,
                OrganisationPermissionsEnum::PROCUREMENT,
                OrganisationPermissionsEnum::HUMAN_RESOURCES,
                OrganisationPermissionsEnum::SUPERVISOR,
                OrganisationPermissionsEnum::ACCOUNTING,
                OrganisationPermissionsEnum::SUPERVISOR_ACCOUNTING,
                OrganisationPermissionsEnum::INVENTORY,

            ],
            RolesEnum::ORG_DIGITAL_AGENCY_ADMIN => [
                OrganisationPermissionsEnum::ORG_REPORTS,
                OrganisationPermissionsEnum::HUMAN_RESOURCES,
                OrganisationPermissionsEnum::SUPERVISOR,
                OrganisationPermissionsEnum::ACCOUNTING,
                OrganisationPermissionsEnum::SUPERVISOR_ACCOUNTING,
                OrganisationPermissionsEnum::SEO,
                OrganisationPermissionsEnum::PPC,
                OrganisationPermissionsEnum::SOCIAL,
                OrganisationPermissionsEnum::SAAS

            ],
            RolesEnum::ORG_AGENT_ADMIN => [
                OrganisationPermissionsEnum::ORG_REPORTS,
                OrganisationPermissionsEnum::PROCUREMENT,
                OrganisationPermissionsEnum::HUMAN_RESOURCES,
                OrganisationPermissionsEnum::SUPERVISOR,
                OrganisationPermissionsEnum::INVENTORY,
                OrganisationPermissionsEnum::ACCOUNTING,
                OrganisationPermissionsEnum::SUPERVISOR_ACCOUNTING
            ],
            RolesEnum::PROCUREMENT_CLERK => [
                OrganisationPermissionsEnum::PROCUREMENT,
                OrganisationPermissionsEnum::INVENTORY
            ],
            RolesEnum::DISPATCH_CLERK => [
                WarehousePermissionsEnum::DISPATCHING,
                OrganisationPermissionsEnum::INVENTORY_VIEW
            ],
            RolesEnum::DISPATCH_SUPERVISOR => [
                WarehousePermissionsEnum::DISPATCHING,
                WarehousePermissionsEnum::SUPERVISOR_DISPATCHING,
                OrganisationPermissionsEnum::INVENTORY_VIEW
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
                ShopPermissionsEnum::ORDERS,
                ShopPermissionsEnum::SUPERVISOR_CRM,
                ShopPermissionsEnum::SUPERVISOR_PRODUCTS
            ],
            RolesEnum::FULFILMENT_SHOP_SUPERVISOR => [
                FulfilmentPermissionsEnum::FULFILMENT_SHOP,
                FulfilmentPermissionsEnum::SUPERVISOR_FULFILMENT_SHOP,
                WarehousePermissionsEnum::FULFILMENT,
            ],
            RolesEnum::FULFILMENT_SHOP_CLERK => [
                FulfilmentPermissionsEnum::FULFILMENT_SHOP,
                WarehousePermissionsEnum::FULFILMENT_VIEW,
            ],
            RolesEnum::FULFILMENT_WAREHOUSE_SUPERVISOR => [
                FulfilmentPermissionsEnum::FULFILMENT_SHOP,
                WarehousePermissionsEnum::FULFILMENT,
            ],
            RolesEnum::FULFILMENT_WAREHOUSE_WORKER => [
                WarehousePermissionsEnum::FULFILMENT,
            ],
            RolesEnum::CUSTOMER_SERVICE_CLERK => [
                ShopPermissionsEnum::CRM,
            ],
            RolesEnum::CUSTOMER_SERVICE_SUPERVISOR => [
                ShopPermissionsEnum::CRM,
                ShopPermissionsEnum::SUPERVISOR_CRM
            ],
            RolesEnum::WAREHOUSE_ADMIN => [
                WarehousePermissionsEnum::LOCATIONS,
                WarehousePermissionsEnum::STOCKS,
                WarehousePermissionsEnum::DISPATCHING,
                WarehousePermissionsEnum::SUPERVISOR_LOCATIONS,
                WarehousePermissionsEnum::SUPERVISOR_STOCKS,
                WarehousePermissionsEnum::SUPERVISOR_DISPATCHING,

            ],
            RolesEnum::STOCK_CONTROLLER => [
                WarehousePermissionsEnum::STOCKS,
                WarehousePermissionsEnum::DISPATCHING,
            ],
            RolesEnum::SEO_SUPERVISOR => [
                OrganisationPermissionsEnum::SEO,
                OrganisationPermissionsEnum::SUPERVISOR_SEO
            ],
            RolesEnum::SEO_CLERK => [
                OrganisationPermissionsEnum::SEO
            ],
            RolesEnum::PPC_SUPERVISOR => [
                OrganisationPermissionsEnum::PPC,
                OrganisationPermissionsEnum::SUPERVISOR_PPC
            ],
            RolesEnum::PPC_CLERK => [
                OrganisationPermissionsEnum::PPC
            ],
            RolesEnum::SOCIAL_SUPERVISOR => [
                OrganisationPermissionsEnum::SOCIAL,
                OrganisationPermissionsEnum::SUPERVISOR_SOCIAL
            ],
            RolesEnum::SOCIAL_CLERK => [
                OrganisationPermissionsEnum::SOCIAL
            ],
            RolesEnum::SAAS_SUPERVISOR => [
                OrganisationPermissionsEnum::SAAS,
                OrganisationPermissionsEnum::SUPERVISOR_SAAS
            ],
            RolesEnum::SAAS_CLERK => [
                OrganisationPermissionsEnum::SAAS
            ],
            RolesEnum::WEBMASTER_CLERK => [
                ShopPermissionsEnum::WEB
            ],
            RolesEnum::WEBMASTER_SUPERVISOR => [
                ShopPermissionsEnum::SUPERVISOR_WEB
            ],
            RolesEnum::SHOPKEEPER_CLERK => [
                ShopPermissionsEnum::PRODUCTS
            ],
            RolesEnum::SHOPKEEPER_SUPERVISOR => [
                ShopPermissionsEnum::PRODUCTS,
                ShopPermissionsEnum::SUPERVISOR_PRODUCTS
            ],
            RolesEnum::MARKETING_CLERK => [
                ShopPermissionsEnum::MARKETING
            ],
            RolesEnum::MARKETING_SUPERVISOR => [
                ShopPermissionsEnum::MARKETING,
                ShopPermissionsEnum::SUPERVISOR_MARKETING
            ],
            RolesEnum::MANUFACTURING_ADMIN => [
                ProductionPermissionsEnum::PRODUCTION_OPERATIONS,
                ProductionPermissionsEnum::PRODUCTION_RD,
                ProductionPermissionsEnum::PRODUCTION_PROCUREMENT,
            ],
            RolesEnum::MANUFACTURING_PRODUCT_DEVELOPER => [
                ProductionPermissionsEnum::PRODUCTION_RD,
            ],
            RolesEnum::MANUFACTURING_ORCHESTRATOR => [
                ProductionPermissionsEnum::PRODUCTION_OPERATIONS,
                ProductionPermissionsEnum::PRODUCTION_PROCUREMENT_VIEW,
            ],
            RolesEnum::MANUFACTURING_LINE_MANAGER => [
                ProductionPermissionsEnum::PRODUCTION_OPERATIONS_EDIT,
                ProductionPermissionsEnum::PRODUCTION_OPERATIONS_VIEW,
                ProductionPermissionsEnum::PRODUCTION_PROCUREMENT_VIEW,
            ],
            RolesEnum::MANUFACTURING_OPERATOR => [
                ProductionPermissionsEnum::PRODUCTION_OPERATIONS_VIEW,
            ],
        };
    }

    public function scope(): string
    {
        return match ($this) {
            RolesEnum::SUPER_ADMIN,
            RolesEnum::SYSTEM_ADMIN,
            RolesEnum::SUPPLY_CHAIN,
            RolesEnum::GOODS_MANAGER,
            RolesEnum::ORGANISATIONS_MANAGER => 'Group',

            RolesEnum::SHOP_ADMIN,
            RolesEnum::CUSTOMER_SERVICE_CLERK,
            RolesEnum::CUSTOMER_SERVICE_SUPERVISOR,

            RolesEnum::WEBMASTER_CLERK,
            RolesEnum::WEBMASTER_SUPERVISOR,

            RolesEnum::SHOPKEEPER_CLERK,
            RolesEnum::SHOPKEEPER_SUPERVISOR,

            RolesEnum::MARKETING_CLERK,
            RolesEnum::MARKETING_SUPERVISOR,

            => 'Shop',
            RolesEnum::FULFILMENT_WAREHOUSE_SUPERVISOR,
            RolesEnum::FULFILMENT_WAREHOUSE_WORKER,
            RolesEnum::WAREHOUSE_ADMIN,
            RolesEnum::DISPATCH_CLERK,
            RolesEnum::DISPATCH_SUPERVISOR,
            RolesEnum::STOCK_CONTROLLER => 'Warehouse',

            RolesEnum::MANUFACTURING_ADMIN,
            RolesEnum::MANUFACTURING_ORCHESTRATOR,
            RolesEnum::MANUFACTURING_LINE_MANAGER,
            RolesEnum::MANUFACTURING_OPERATOR,
            RolesEnum::MANUFACTURING_PRODUCT_DEVELOPER,


            => 'Production',
            RolesEnum::FULFILMENT_SHOP_SUPERVISOR,
            RolesEnum::FULFILMENT_SHOP_CLERK,
            => 'Fulfilment',
            default => 'Organisation'
        };
    }


    public function scopeTypes(): array
    {
        return match ($this) {
            RolesEnum::ORG_SHOP_ADMIN,
            RolesEnum::FULFILMENT_SHOP_SUPERVISOR,
            RolesEnum::FULFILMENT_SHOP_CLERK,
            RolesEnum::FULFILMENT_WAREHOUSE_SUPERVISOR,
            RolesEnum::FULFILMENT_WAREHOUSE_WORKER,
            RolesEnum::MANUFACTURING_ADMIN,
            RolesEnum::MANUFACTURING_ORCHESTRATOR,
            RolesEnum::MANUFACTURING_LINE_MANAGER,
            RolesEnum::MANUFACTURING_OPERATOR,
            RolesEnum::MANUFACTURING_PRODUCT_DEVELOPER,


            => [OrganisationTypeEnum::SHOP],
            RolesEnum::ORG_DIGITAL_AGENCY_ADMIN,
            RolesEnum::SEO_SUPERVISOR,
            RolesEnum::SEO_CLERK,
            RolesEnum::PPC_SUPERVISOR,
            RolesEnum::PPC_CLERK,
            RolesEnum::SOCIAL_SUPERVISOR,
            RolesEnum::SOCIAL_CLERK,
            RolesEnum::SAAS_SUPERVISOR,
            RolesEnum::SAAS_CLERK,

            => [OrganisationTypeEnum::DIGITAL_AGENCY],
            RolesEnum::ORG_AGENT_ADMIN,
            => [OrganisationTypeEnum::AGENT],
            RolesEnum::PROCUREMENT_CLERK,
            RolesEnum::PROCUREMENT_SUPERVISOR,
            RolesEnum::DISPATCH_CLERK,
            RolesEnum::DISPATCH_SUPERVISOR,
            RolesEnum::WAREHOUSE_ADMIN,
            RolesEnum::STOCK_CONTROLLER,

            => [OrganisationTypeEnum::AGENT, OrganisationTypeEnum::SHOP],
            RolesEnum::SHOP_ADMIN,

            => [OrganisationTypeEnum::DIGITAL_AGENCY, OrganisationTypeEnum::SHOP],
            default => [OrganisationTypeEnum::DIGITAL_AGENCY, OrganisationTypeEnum::AGENT, OrganisationTypeEnum::SHOP]
        };
    }

    public static function getRolesWithScope(Group|Organisation|Shop|Warehouse|Fulfilment|Production $scope): array
    {
        $roles = array_filter(RolesEnum::cases(), fn ($role) => $role->scope() == class_basename($scope));


        $rolesNames = [];
        foreach ($roles as $case) {
            $skip = false;
            if ($scope instanceof Organisation) {
                if (!in_array($scope->type, $case->scopeTypes())) {
                    $skip = true;
                }
            }
            if (!$skip) {
                $rolesNames[] = self::getRoleName($case->value, $scope);
            }
        }


        return $rolesNames;
    }


    public static function getRoleName(string $rawName, Group|Organisation|Shop|Warehouse|Fulfilment|Production $scope): string
    {
        return match (class_basename($scope)) {
            'Organisation', 'Shop', 'Warehouse', 'Fulfilment', 'Production' => $rawName.'-'.$scope->id,
            default => $rawName
        };
    }


}
