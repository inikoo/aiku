<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 31 May 2024 17:13:33 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Helpers\Barcode\BarcodeStatusEnum;
use App\Enums\Helpers\Barcode\BarcodeTypeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraBarcode extends FetchAurora
{
    protected function parseModel(): void
    {
        $status = match ($this->auroraModelData->{'Barcode Status'}) {
            'Used'     => BarcodeStatusEnum::USED,
            'Reserved' => BarcodeStatusEnum::RESERVED,
            default    => BarcodeStatusEnum::AVAILABLE
        };

        $this->parsedData['barcode'] = [
            'number'      => $this->auroraModelData->{'Barcode Number'},
            'source_id'   => $this->organisation->id.':'.$this->auroraModelData->{'Barcode Key'},
            'type'        => BarcodeTypeEnum::EAN,
            'status'      => $status,
            'note'        => $this->auroraModelData->{'Barcode Sticky Note'},
            'assigned_at' => $this->auroraModelData->{'Barcode Used From'} ? $this->parseDatetime($this->auroraModelData->{'Barcode Used From'}) : null
        ];

    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Barcode Dimension')
            ->where('Barcode Key', $id)->first();
    }
}