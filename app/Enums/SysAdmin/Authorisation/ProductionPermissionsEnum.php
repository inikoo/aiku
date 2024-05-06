<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 May 2024 12:17:52 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\SysAdmin\Authorisation;

use App\Models\Manufacturing\Production;

enum ProductionPermissionsEnum: string
{
    case PRODUCTION = 'productions';

    case PRODUCTION_EDIT = 'productions.edit';

    case PRODUCTION_VIEW = 'productions.view';





    case SUPERVISOR_PRODUCTIONS        = 'supervisor-productions';


    public static function getAllValues(Production $production): array
    {

        $rawPermissionsNames = array_column(ProductionPermissionsEnum::cases(), 'value');

        $permissionsNames    = [];
        foreach ($rawPermissionsNames as $rawPermissionsName) {
            $permissionsNames[] = self::getPermissionName($rawPermissionsName, $production);
        }

        return $permissionsNames;
    }

    public static function getPermissionName(string $rawName, Production $production): string
    {
        $permissionComponents = explode('.', $rawName);
        $permissionComponents = array_merge(array_slice($permissionComponents, 0, 1), [$production->id], array_slice($permissionComponents, 1));

        return join('.', $permissionComponents);
    }

}
