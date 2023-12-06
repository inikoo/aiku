<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 06 Dec 2023 00:49:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\SysAdmin\Authorisation;

enum OrganisationPermissionsEnum: string
{
    case ORG_BUSINESS_INTELLIGENCE = 'org-business-intelligence';

    case PROCUREMENT = 'procurement';

    case PROCUREMENT_EDIT = 'procurement.edit';

    case PROCUREMENT_VIEW = 'procurement.view';


    public static function getAllValues(): array
    {
        return array_column(OrganisationPermissionsEnum::cases(), 'value');
    }


}
