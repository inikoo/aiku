<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 12 Oct 2023 15:56:49 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\AuditResolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class AuditGroupResolver implements Resolver
{
    public static function resolve(Auditable $auditable)
    {
        if (app()->bound('group')) {
            return app('group')->id;
        }

        if($auditable->group_id) {
            return $auditable->group_id;
        }

        if(class_basename($auditable)=='Group') {
            return $auditable->id;
        }

        return null;
    }
}
