<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 16:37:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Procurement\AgentOrganisation;

use App\Enums\EnumHelperTrait;

enum AgentOrganisationStatusEnum: string
{
    use EnumHelperTrait;

    case OWNER        = 'owner';
    case ADOPTED      = 'adopted';
    case AVAILABLE    = 'available';


}
