<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 02 Sept 2024 12:26:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Ordering\ShippingZoneSchema\StoreShippingZoneSchema;
use App\Actions\Ordering\ShippingZoneSchema\UpdateShippingZoneSchema;
use App\Models\Ordering\ShippingZoneSchema;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraShippingZoneSchemas extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:shipping_zone_schemas {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?ShippingZoneSchema
    {
        if ($shippingZoneSchemaData = $organisationSource->fetchShippingZoneSchema($organisationSourceId)) {
            if ($shippingZoneSchema = ShippingZoneSchema::where('source_id', $shippingZoneSchemaData['shipping-zone-schema']['source_id'])->first()) {
                $shippingZoneSchema = UpdateShippingZoneSchema::make()->action(
                    shippingZoneSchema: $shippingZoneSchema,
                    modelData: $shippingZoneSchemaData['shipping-zone-schema'],
                    audit:false
                );
            } else {

                $shippingZoneSchema = StoreShippingZoneSchema::make()->action(
                    shop: $shippingZoneSchemaData['shop'],
                    modelData: $shippingZoneSchemaData['shipping-zone-schema'],
                );
            }

            return $shippingZoneSchema;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Shipping Zone Schema Dimension')
            ->select('Shipping Zone Schema Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Shipping Zone Schema Dimension')->count();
    }


}
