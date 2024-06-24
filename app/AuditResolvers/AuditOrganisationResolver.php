<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Jun 2024 20:07:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\AuditResolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class AuditOrganisationResolver implements Resolver
{
    public static function resolve(Auditable $auditable)
    {

        if($auditable->organisation_id) {
            return $auditable->organisation_id;
        }

        if(class_basename($auditable)=='Organisation') {
            return $auditable->id;
        }

        return null;
    }
}
