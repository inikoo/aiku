<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 06 Dec 2023 00:49:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\SysAdmin\Authorisation;

use App\Models\SysAdmin\Organisation;

enum OrganisationPermissionsEnum: string
{
    case ORG_BUSINESS_INTELLIGENCE = 'org-business-intelligence';

    case INVENTORY      = 'inventory';
    case INVENTORY_EDIT = 'inventory.edit';
    case INVENTORY_VIEW = 'inventory.view';

    case INVENTORY_WAREHOUSE_EDIT = 'inventory.warehouse.edit';
    case INVENTORY_WAREHOUSE_VIEW = 'inventory.warehouse.view';

    case INVENTORY_STOCK_EDIT = 'inventory.stock.edit';
    case INVENTORY_STOCK_VIEW = 'inventory.stock.view';


    case ACCOUNTING      = 'accounting';
    case ACCOUNTING_EDIT = 'accounting.edit';
    case ACCOUNTING_VIEW = 'accounting.view';

    case HUMAN_RESOURCES      = 'human-resources';
    case HUMAN_RESOURCES_EDIT = 'human-resources.edit';
    case HUMAN_RESOURCES_VIEW = 'human-resources.view';

    case PROCUREMENT      = 'procurement';
    case PROCUREMENT_EDIT = 'procurement.edit';
    case PROCUREMENT_VIEW = 'procurement.view';

    case DISPATCHING      = 'dispatching';
    case DISPATCHING_EDIT = 'dispatching.edit';
    case DISPATCHING_VIEW = 'dispatching.view';


    case SUPERVISOR_HUMAN_RESOURCES = 'supervisor-human-resources';
    case SUPERVISOR_ACCOUNTING      = 'supervisor-accounting';
    case SUPERVISOR_PROCUREMENT     = 'supervisor-procurement';
    case SUPERVISOR_INVENTORY       = 'supervisor-inventory';


    public static function getAllValues(Organisation $organisation): array
    {

        $rawPermissionsNames = array_column(OrganisationPermissionsEnum::cases(), 'value');

        $permissionsNames    = [];
        foreach ($rawPermissionsNames as $rawPermissionsName) {
            $permissionsNames[] = self::getPermissionName($rawPermissionsName, $organisation);
        }

        return $permissionsNames;
    }

    public static function getPermissionName(string $rawName, Organisation $organisation): string
    {
        $permissionComponents = explode('.', $rawName);
        $permissionComponents = array_merge(array_slice($permissionComponents, 0, 1), [$organisation->slug], array_slice($permissionComponents, 1));

        return join('.', $permissionComponents);
    }

}
