<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Jun 2024 20:11:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\AuditResolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class AuditCustomerResolver implements Resolver
{
    public static function resolve(Auditable $auditable)
    {

        if($auditable->customer_id) {
            return $auditable->customer_id;
        }

        if(class_basename($auditable)=='Customer') {
            return $auditable->id;
        }

        return null;
    }
}
