<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Jul 2024 00:45:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Audits\Resolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class AuditShopResolver implements Resolver
{
    public static function resolve(Auditable $auditable)
    {

        if($auditable->shop_id) {
            return $auditable->shop_id;
        }

        if(class_basename($auditable)=='Shop') {
            return $auditable->id;
        }

        return null;
    }
}
