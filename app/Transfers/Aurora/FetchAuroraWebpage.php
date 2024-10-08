<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraWebpage extends FetchAurora
{
    use WithAuroraProcessWebpage;

    public function fetch(int $id): ?array
    {
        $this->auroraModelData = $this->fetchData($id);

        $this->parseModel();

        return $this->parsedData;
    }

    protected function parseModel(): void
    {
        $typeData = DB::connection('aurora')
            ->table('Webpage Type Dimension')
            ->select('Webpage Type Code')
            ->where('Webpage Type Key', $this->auroraModelData->{'Webpage Type Key'})->first();


        if ($typeData and $typeData->{'Webpage Type Code'} == 'Sys') {
            return;
        }

        if ($typeData->{'Webpage Type Code'} == 'Prod' and $this->auroraModelData->{'Webpage Scope Key'} == '') {
            return;
        }



        if (preg_match('/\.sys$/', $this->auroraModelData->{'Webpage Code'})) {
            return;
        }
        if (preg_match('/^web\./i', $this->auroraModelData->{'Webpage Code'})) {
            return;
        }

        if (preg_match('/^fam\./i', $this->auroraModelData->{'Webpage Code'})) {
            return;
        }

        if (preg_match('/^dept\./i', $this->auroraModelData->{'Webpage Code'})) {
            return;
        }

        if (preg_match('/blog/i', $this->auroraModelData->{'Webpage Code'})) {
            return;
        }

        $parsedData = $this->processAuroraWebpage($this->organisation, $this->auroraModelData);

        if (!$parsedData) {
            return;
        }
        $this->parsedData['website'] = $parsedData['website'];
        $this->parsedData['webpage'] = $parsedData['webpage'];

        //dd($this->parsedData['webpage']);


    }




    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Page Store Dimension')
            ->where('aiku_ignore', 'No')
            ->where('Page Key', $id)->first();
    }
}
