<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 06 Dec 2023 00:49:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\SysAdmin\Authorisation;

enum GroupPermissionsEnum: string
{
    case GROUP_REPORTS               = 'group-reports';
    case SYSADMIN                    = 'sysadmin';
    case SYSADMIN_EDIT               = 'sysadmin.edit';
    case SYSADMIN_VIEW               = 'sysadmin.view';

    case ORGANISATIONS      = 'organisations';
    case ORGANISATIONS_VIEW = 'organisations.edit';

    case ORGANISATIONS_EDIT = 'organisations.view';

    case GOODS      = 'goods';
    case GOODS_VIEW = 'goods.edit';

    case GOODS_EDIT          = 'goods.view';
    case SUPPLY_CHAIN        = 'supply-chain';
    case SUPPLY_CHAIN_EDIT   = 'supply-chain.edit';
    case SUPPLY_CHAIN_VIEW   = 'supply-chain.view';


    public static function getAllValues(): array
    {
        return array_column(GroupPermissionsEnum::cases(), 'value');
    }


}
