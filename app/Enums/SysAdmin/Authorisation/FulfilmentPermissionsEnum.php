<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Jan 2024 20:08:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\SysAdmin\Authorisation;

use App\Models\Fulfilment\Fulfilment;

enum FulfilmentPermissionsEnum: string
{
    case FULFILMENT = 'fulfilment';

    case FULFILMENT_VIEW = 'fulfilment.view';

    case FULFILMENT_EDIT = 'fulfilment.edit';

    case STORED_ITEMS      = 'fulfilment.stored-items';
    case STORED_ITEMS_EDIT = 'fulfilment.stored-items.edit';

    case STORED_ITEMS_VIEW = 'fulfilment.stored-items.view';

    case SUPERVISOR_FULFILMENT   = 'supervisor-fulfilment';
    case SUPERVISOR_STORED_ITEMS = 'supervisor-stored-items';

    public static function getAllValues(Fulfilment $fulfilment): array
    {
        $rawPermissionsNames = array_column(FulfilmentPermissionsEnum::cases(), 'value');

        $permissionsNames = [];
        foreach ($rawPermissionsNames as $rawPermissionsName) {
            $permissionsNames[] = self::getPermissionName($rawPermissionsName, $fulfilment);
        }

        return $permissionsNames;
    }

    public static function getPermissionName(string $rawName, Fulfilment $fulfilment): string
    {
        $permissionComponents = explode('.', $rawName);
        $permissionComponents = array_merge(array_slice($permissionComponents, 0, 1), [$fulfilment->id], array_slice($permissionComponents, 1));

        return join('.', $permissionComponents);
    }

}
