<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Aug 2024 17:07:40 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\Stock\UI;

use App\Http\Resources\Goods\StocksResource;
use App\Models\SupplyChain\Stock;
use Lorisleiva\Actions\Concerns\AsObject;

class GetStockShowcase
{
    use AsObject;

    public function handle(Stock $stock): \Illuminate\Support\Collection
    {
        return collect(
            [
                'contactCard'              => StocksResource::make($stock)->getArray(),
                'locationRoute'            => [
                    'name'       => 'grp.org.warehouses.show.infrastructure.locations.index',
                    'parameters' => [
                        'organisation' => null,
                        'warehouse'    => null
                    ]
                ],
                'associateLocationRoute'  => [
                    'method'     => 'post',
                    'name'       => 'grp.models.org_stock.location.store',
                    'parameters' => [
                        'orgStock' => null
                    ]
                ],
                'disassociateLocationRoute' => [
                    'method'    => 'delete',
                    'name'      => 'grp.models.location_org_stock.delete',
                ],
                'auditRoute' => [
                    'method'    => 'patch',
                    'name'      => 'grp.models.location_org_stock.audit',
                ],
                'moveLocationRoute' => [
                    'method'    => 'patch',
                    'name'      => 'grp.models.location_org_stock.move',
                ]
            ]
        );
    }
}
