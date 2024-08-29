<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Aug 2024 17:14:05 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock\UI;

use App\Http\Resources\Inventory\OrgStockResource;
use App\Models\Inventory\OrgStock;
use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\Concerns\AsObject;

class GetOrgStockShowcase
{
    use AsObject;

    public function handle(Warehouse $warehouse, OrgStock $orgStock)
    {
        $orgStock->load('locations');

        return collect(
            [
                'contactCard'              => OrgStockResource::make($orgStock)->getArray(),
                'locationRoute'            => [
                    'name'       => 'grp.org.warehouses.show.infrastructure.locations.index',
                    'parameters' => [
                        'organisation' => $warehouse->organisation->slug,
                        'warehouse'    => $warehouse->slug
                    ]
                ],
                'associateLocationRoute'  => [
                    'method'     => 'post',
                    'name'       => 'grp.models.org_stock.location.store',
                    'parameters' => [
                        'orgStock' => $orgStock->id
                    ]
                ],
                'disassociateLocationRoute' => [
                    'method'    => 'delete',
                    'name'      => 'grp.models.location_org_stock.delete',
                    'parameters'=> [] //need locationorgstock param
                ],
                'auditLocationRoute' => [
                    'method'    => 'patch',
                    'name'      => 'grp.models.location_org_stock.audit',
                    'parameters'=> [] //need locationorgstock param
                ],
                'moveLocationRoute' => [
                    'method'    => 'patch',
                    'name'      => 'grp.models.location_org_stock.move',
                    'parameters'=> [] //need 2 locationorgstock param
                ]
            ]
        );
    }
}
