<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Jun 2023 18:49:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Enums\SysAdmin\User;

use App\Enums\EnumHelperTrait;

enum SynchronisableUserFieldsEnum: string
{
    use EnumHelperTrait;

    case USERNAME         = 'username';
    case PASSWORD         = 'password';
    case LEGACY_PASSWORD  = 'legacy_password';
    case AUTH_TYPE        = 'auth_type';
    case AVATAR           = 'avatar_id';

}
