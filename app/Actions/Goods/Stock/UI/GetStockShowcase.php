<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Aug 2024 17:07:40 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\Stock\UI;

use App\Models\Goods\Stock;
use Lorisleiva\Actions\Concerns\AsObject;

class GetStockShowcase
{
    use AsObject;

    public function handle(Stock $stock): array
    {
        $numberLocations   = 0;
        $quantityLocations = 0;
        foreach ($stock->orgStocks as $orgStock) {
            $num               = $orgStock->locationOrgStocks()->count();
            $quantity          = $orgStock->quantity_in_locations;
            $quantityLocations = $quantityLocations   + $quantity;
            $numberLocations   = $numberLocations     + $num;
        }

        return [
             'contactCard' => [
                 'id'                 => $stock->id,
                 'slug'               => $stock->slug,
                 'code'               => $stock->slug,
                 'unit_value'         => $stock->unit_value,
                 'description'        => $stock->description,
                 'number_locations'   => $numberLocations,
                 'quantity_locations' => $quantityLocations,
                 'photo'              => $stock->imageSources(),
                //  'locations'          => LocationOrgStocksResource::collection($stock->orgStocks->first()->locationOrgStocks)
             ],
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
        ];
    }
}
