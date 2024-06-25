<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Jun 2024 20:09:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\AuditResolvers;

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
