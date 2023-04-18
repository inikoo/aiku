<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 09:27:08 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


namespace App\Imports;

use App\Actions\Helpers\TariffCode\StoreTariffCode;
use App\Actions\Helpers\TariffCode\UpdateTariffCode;
use App\Models\Helpers\TariffCode;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TariffCodeImport implements ToCollection
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
