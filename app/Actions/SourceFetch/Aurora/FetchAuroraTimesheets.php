<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 30 Apr 2024 13:06:26 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\HumanResources\Clocking\StoreClocking;
use App\Actions\HumanResources\Timesheet\StoreTimesheet;
use App\Actions\HumanResources\Timesheet\UpdateTimesheet;
use App\Models\HumanResources\Timesheet;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraTimesheets extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:timesheets {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Timesheet
    {
        if ($timeSheetData = $organisationSource->fetchTimesheet($organisationSourceId)) {

            if ($timesheet = Timesheet::where('source_id', $timeSheetData['timesheet']['source_id'])->first()) {
                $timesheet = UpdateTimesheet::make()->action(
                    timesheet: $timesheet,
                    modelData: $timeSheetData['timesheet']
                );
            } else {
                $timesheet = StoreTimesheet::make()->action(
                    parent: $timeSheetData['employee'],
                    modelData: $timeSheetData['timesheet'],
                );

                foreach ($timeSheetData['clockings'] as $clockingData) {
                    StoreClocking::make()->action(
                        generator: $clockingData['generator'],
                        parent: $clockingData['parent'],
                        subject: $clockingData['subject'],
                        modelData: $clockingData['clockingData']
                    );
                }


            }

            return $timesheet;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Timesheet Dimension')
            ->select('Timesheet Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Timesheet Dimension')->count();
    }


}
