<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Jul 2024 00:45:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Audits\Resolvers;

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
