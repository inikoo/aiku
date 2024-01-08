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



    case ACCOUNTING      = 'accounting';
    case ACCOUNTING_EDIT = 'accounting.edit';
    case ACCOUNTING_VIEW = 'accounting.view';

    case HUMAN_RESOURCES      = 'human-resources';
    case HUMAN_RESOURCES_EDIT = 'human-resources.edit';
    case HUMAN_RESOURCES_VIEW = 'human-resources.view';

    case PROCUREMENT      = 'procurement';
    case PROCUREMENT_EDIT = 'procurement.edit';
    case PROCUREMENT_VIEW = 'procurement.view';


    case INVENTORIES      = 'inventories';
    case INVENTORIES_EDIT = 'inventories.edit';
    case INVENTORIES_VIEW = 'inventories.view';


    case SHOPS      = 'shops';
    case SHOPS_EDIT = 'shops.edit';
    case SHOPS_VIEW = 'shops.view';

    case WAREHOUSES      = 'warehouses';
    case WAREHOUSES_EDIT = 'warehouses.edit';
    case WAREHOUSES_VIEW = 'warehouses.view';

    case SUPERVISOR_HUMAN_RESOURCES = 'supervisor-human-resources';
    case SUPERVISOR_ACCOUNTING      = 'supervisor-accounting';
    case SUPERVISOR_PROCUREMENT     = 'supervisor-procurement';
    //   case SUPERVISOR_INVENTORIES       = 'supervisor-inventories';


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
