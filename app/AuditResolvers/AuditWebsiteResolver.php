<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Jun 2024 20:10:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\AuditResolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class AuditWebsiteResolver implements Resolver
{
    public static function resolve(Auditable $auditable)
    {

        if($auditable->website_id) {
            return $auditable->website_id;
        }

        if(class_basename($auditable)=='Website') {
            return $auditable->id;
        }

        return null;
    }
}
