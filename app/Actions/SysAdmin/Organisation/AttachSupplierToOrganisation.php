<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 21:06:49 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Actions\OrgAction;
use App\Models\SupplyChain\Supplier;
use App\Models\SysAdmin\Organisation;

class AttachSupplierToOrganisation extends OrgAction
{
    public function handle(Organisation $organisation, Supplier $supplier, array $modelData = []): Organisation
    {
        $organisation->suppliers()->attach($supplier, $modelData);

        return $organisation;
    }

}
