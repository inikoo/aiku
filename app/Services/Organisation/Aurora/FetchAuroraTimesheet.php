<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 13:14:16 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Models\HumanResources\Workplace;
use Illuminate\Support\Facades\DB;

class FetchAuroraTimesheet extends FetchAurora
{
    use WithAuroraImages;
    use WithAuroraParsers;


    public function fetch(int $id): ?array
    {
        $this->auroraModelData = $this->fetchData($id);
        if ($this->auroraModelData and $this->auroraModelData->{'Staff Type'} == 'Employee') {
            $this->parseModel();
        }

        return $this->parsedData;
    }

    protected function parseModel(): void
    {


        $clockingsData = DB::connection('aurora')
            ->table('Timesheet Record Dimension')
            ->where('Timesheet Record Timesheet Key', $this->auroraModelData->{'Timesheet Key'})
            ->where('Timesheet Record Type', 'ClockingRecord')
            ->where('Timesheet Record Ignored', 'No')
            ->orderBy('Timesheet Record Date')->get();

        if ($clockingsData->count() == 0) {
            return;
        }

        if ($this->auroraModelData->{'Staff Type'} == 'Employee') {
            $this->parsedData['employee'] = $this->parseEmployee($this->auroraModelData->{'Timesheet Staff Key'});
        } else {
            return;
        }


        $this->parsedData['timesheet'] = [
            'date'      => $this->auroraModelData->{'Timesheet Date'},
            'source_id' => $this->organisation->id.':'.$this->auroraModelData->{'Timesheet Key'},
        ];

        foreach ($clockingsData as $clockingsDatum) {
            $parsedClocking = [
                'clocked_at' => $clockingsDatum->{'Timesheet Record Date'},
                'source_id'  => $this->organisation->id.':'.$clockingsDatum->{'Timesheet Record Key'},
            ];

            $generator = $this->organisation;
            if ($clockingsDatum->{'Timesheet Record Source'} == 'WorkHome') {
                $generator = $this->parsedData['employee'];
            }

            $parent = Workplace::first();

            if ($clockingsDatum->{'Timesheet Record Source'} == 'ClockingMachine') {
                $parent=$this->parseClockingMachine($this->organisation->id.':'.$clockingsDatum->{'Timesheet Record Source Key'});
            }

            $parsedClocking['generator'] = $generator;
            $parsedClocking['parent']    = $parent;


            $this->parsedData['clockings'][] = [
                'parent'       => $parent,
                'generator'    => $generator,
                'subject'      => $this->parsedData['employee'],
                'clockingData' => $parsedClocking
            ];
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Timesheet Dimension')
            ->leftJoin('Staff Dimension', 'Timesheet Staff Key', '=', 'Staff Key')
            ->where('Timesheet Key', $id)->first();
    }

}
