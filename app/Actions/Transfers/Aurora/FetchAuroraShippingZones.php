<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 02 Sept 2024 12:26:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Ordering\ShippingZone\StoreShippingZone;
use App\Actions\Ordering\ShippingZone\UpdateShippingZone;
use App\Models\Ordering\ShippingZone;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraShippingZones extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:shipping_zones {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?ShippingZone
    {
        if ($shippingZoneData = $organisationSource->fetchShippingZone($organisationSourceId)) {
            if ($shippingZone = ShippingZone::where('source_id', $shippingZoneData['shipping-zone']['source_id'])->first()) {
                $shippingZone = UpdateShippingZone::make()->action(
                    shippingZone: $shippingZone,
                    modelData: $shippingZoneData['shipping-zone'],
                    strict: false,
                    audit:false
                );
            } else {

                $shippingZone = StoreShippingZone::make()->action(
                    shippingZoneSchema: $shippingZoneData['shipping-zone-schema'],
                    modelData: $shippingZoneData['shipping-zone'],
                    strict: false,
                );
            }

            return $shippingZone;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Shipping Zone Dimension')
            ->select('Shipping Zone Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Shipping Zone Dimension')->count();
    }


}
