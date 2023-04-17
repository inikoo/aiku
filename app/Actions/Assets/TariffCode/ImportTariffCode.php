<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 18 Apr 2023 11:01:57 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


namespace App\Actions\Assets\TariffCode;

use App\Models\Assets\TariffCode;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ImportTariffCode implements ToCollection
{
    public function collection(Collection $rows): void
    {
        $i = 0;
        foreach ($rows as $row) {
            if ($i > 0) {
                $parent = TariffCode::where('hs_code', $row[3])->first();
                $tariffCodeData = [
                    'section' => $row[0],
                    'hs_code' => $row[1],
                    'description' => $row[2],
                    'parent_id' => $parent->id ?? null,
                    'level' => $row[4],
                ];

                $tariffCode = TariffCode::where('hs_code', $row[1])->first();
                if($tariffCode) {
                    UpdateTariffCode::run($tariffCode, $tariffCodeData);
                } else {
                    StoreTariffCode::run($tariffCodeData);
                }
            }
            $i++;
        }
    }
}
