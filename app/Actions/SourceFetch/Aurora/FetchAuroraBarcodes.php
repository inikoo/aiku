<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 31 May 2024 17:14:23 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Helpers\Barcode\StoreBarcode;
use App\Actions\Helpers\Barcode\UpdateBarcode;
use App\Models\Helpers\Barcode;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraBarcodes extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:barcodes {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Barcode
    {
        if ($barcodeData = $organisationSource->fetchBarcode($organisationSourceId)) {

            if ($barcode = Barcode::where('source_id', $barcodeData['barcode']['source_id'])->first()) {
                $barcode = UpdateBarcode::make()->action(
                    barcode: $barcode,
                    modelData: $barcodeData['barcode']
                );
            } else {
                $barcode = StoreBarcode::make()->action(
                    group: $organisationSource->getOrganisation()->group,
                    modelData: $barcodeData['barcode'],
                );
            }

            return $barcode;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Barcode Dimension')
            ->select('Barcode Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Barcode Dimension')->count();
    }


}
