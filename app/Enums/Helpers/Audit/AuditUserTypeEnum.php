<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 24 Dec 2024 12:23:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Enums\Helpers\Audit;

use App\Enums\EnumHelperTrait;

enum AuditUserTypeEnum: string
{
    use EnumHelperTrait;

    case SYSTEM = 'system';
    case USER = 'user';
    case WEB_USER = 'web_user';
    case OTHER = 'other';

}
